<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Backend;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\CoreBundle\Exception\ResponseException;
use Contao\DataContainer;
use Contao\Message;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationNotifier;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationType;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\SiteSettingsProvider;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Called through the module keys, Contao has already checked the module access.
 */
class DeclarationActions
{
    public function __construct(
        private readonly DeclarationRepository $repository,
        private readonly DeclarationNotifier $notifier,
        private readonly SiteSettingsProvider $settings,
        private readonly CsvExporter $exporter,
        private readonly RequestStack $requestStack,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function export(): never
    {
        $response = new Response($this->exporter->export($this->repository->findAll()));
        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'widerrufe-' . date('Y-m-d') . '.csv'));
        $response->setPrivate();

        throw new ResponseException($response);
    }

    public function resend(DataContainer $dc): never
    {
        $declaration = $this->repository->find((int) $dc->id);

        if ($declaration) {
            if ($this->notifier->send($declaration, $this->settings->get($declaration->rootPage))) {
                Message::addConfirmation($this->translator->trans('backend.resent', [], DeclarationType::TRANSLATION_DOMAIN));
            } else {
                Message::addError($this->translator->trans('backend.resend_failed', [], DeclarationType::TRANSLATION_DOMAIN));
            }
        }

        $request = $this->requestStack->getCurrentRequest();

        throw new RedirectResponseException($request->getSchemeAndHttpHost() . $request->getBaseUrl() . $request->getPathInfo() . '?do=right_of_withdrawal');
    }
}
