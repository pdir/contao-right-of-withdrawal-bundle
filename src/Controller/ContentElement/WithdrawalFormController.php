<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Controller\ContentElement;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationInput;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationSubmitter;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationValidator;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The second and last step of the process: the form holds the required
 * information and the confirmation button, pressing it submits the declaration.
 */
#[AsContentElement('withdrawal_form', category: 'miscellaneous')]
class WithdrawalFormController extends AbstractContentElementController
{
    public const RECEIPT_PARAMETER = 'receipt';

    /**
     * How long the link to a receipt works, the consumer keeps the mail anyway.
     */
    public const RECEIPT_LIFETIME = 30 * 86400;

    public function __construct(
        private readonly SiteSettingsProvider $settings,
        private readonly DeclarationValidator $validator,
        private readonly DeclarationSubmitter $submitter,
        private readonly DeclarationRepository $repository,
        private readonly TranslatorInterface $translator,
    ) {
    }

    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $type = DeclarationType::tryFrom((string) $model->rowFunction) ?? DeclarationType::Withdrawal;

        $template->set('function', $type->value);
        $template->set('confirm_label', $this->trans($type->confirmLabelKey()));
        $template->set('entry_label', $this->trans($type->entryLabelKey()));

        if ($this->isBackendScope($request)) {
            $template->set('is_backend', true);

            return $template->getResponse();
        }

        $page = $this->getPageModel();

        if (null === $page) {
            return new Response();
        }

        $page->loadDetails();
        $settings = $this->settings->get((int) $page->rootId);

        if (!$settings->jurisdiction->supports($type)) {
            return new Response();
        }

        $formId = 'withdrawal_form_' . $model->id;
        $token = (string) $request->query->get(self::RECEIPT_PARAMETER, '');

        if ('' !== $token) {
            $declaration = $this->repository->findByToken($token);

            if ($declaration && $declaration->rootPage === $settings->rootPage && $declaration->receivedAt > time() - self::RECEIPT_LIFETIME) {
                $template->set('declaration', $declaration);
                $template->set('receipt_token', $token);
            }

            // The response contains personal data and must never reach a cache
            return $this->uncached($template->getResponse());
        }

        $input = new DeclarationInput(contract: trim((string) $request->query->get('contract', '')));
        $errors = [];

        if ($request->isMethod('POST') && $formId === $request->request->get('FORM_SUBMIT')) {
            $input = DeclarationInput::fromRequest($request);
            $errors = array_map(fn (string $key): string => $this->trans($key), $this->validator->validate($type, $input));

            if ('' !== (string) $request->request->get('website', '')) {
                $errors['form'] = $this->trans('error.not_submitted');
            } elseif (!$errors && $this->submitter->isRateLimited($request->getClientIp())) {
                $errors['form'] = '' !== $settings->recipient
                    ? $this->trans('error.rate_limited_contact', ['%recipient%' => $settings->recipient])
                    : $this->trans('error.rate_limited');
            }

            if (!$errors) {
                $declaration = $this->submitter->submit($type, $input, $settings, $request->getClientIp());

                throw new RedirectResponseException($request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . '?' . self::RECEIPT_PARAMETER . '=' . $declaration->token, Response::HTTP_SEE_OTHER);
            }
        }

        $template->set('form_id', $formId);
        $template->set('input', $input);
        $template->set('errors', $errors);

        $response = $template->getResponse();

        return $request->isMethod('POST') ? $this->uncached($response) : $response;
    }

    /**
     * @param array<string, string> $parameters
     */
    private function trans(string $key, array $parameters = []): string
    {
        return $this->translator->trans($key, $parameters, DeclarationType::TRANSLATION_DOMAIN);
    }

    private function uncached(Response $response): Response
    {
        // Otherwise the page would ignore the cache headers of this fragment
        $this->markResponseForInternalCaching($response);

        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');

        return $response;
    }
}
