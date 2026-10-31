<?php

namespace Pdir\ContaoRightOfWithdrawalBundle\Controller;

use Pdir\ContaoRightOfWithdrawalBundle\Controller\ContentElement\WithdrawalFormController;
use Pdir\ContaoRightOfWithdrawalBundle\Declaration\DeclarationRepository;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Translation\LocaleSwitcher;
use Twig\Environment;

/**
 * Lets the consumer save the declaration on a durable medium (§ 312k Abs. 3 BGB).
 */
class ReceiptController
{
    public function __construct(
        private readonly DeclarationRepository $repository,
        private readonly Environment $twig,
        private readonly LocaleSwitcher $localeSwitcher,
    ) {
    }

    public function __invoke(string $token): Response
    {
        $declaration = $this->repository->findByToken($token);

        if (null === $declaration || $declaration->receivedAt <= time() - WithdrawalFormController::RECEIPT_LIFETIME) {
            throw new NotFoundHttpException();
        }

        // This route has no page, the language comes from the declaration
        $response = new Response($this->localeSwitcher->runWithLocale(
            $declaration->language,
            fn (): string => $this->twig->render('@PdirContaoRightOfWithdrawal/receipt/receipt.html.twig', ['declaration' => $declaration]),
        ));
        $response->setPrivate();
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->set('X-Robots-Tag', 'noindex');
        $response->headers->set('Content-Disposition', HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            \sprintf('%s-%d.html', $declaration->type->value, $declaration->sequenceNumber),
        ));

        return $response;
    }
}
