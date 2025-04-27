<?php declare(strict_types=1);

namespace RefundOrderButton\Controller;

use Shopware\Core\Checkout\Cart\SalesChannel\AbstractCartOrderRoute;
use Shopware\Core\Checkout\Order\SalesChannel\CancelOrderRoute;
use Shopware\Core\Framework\Routing\Attribute\Since;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Checkout\Order\Event\OrderStateMachineStateChangeEvent;

#[Route(defaults: ['_routeScope' => ['storefront']])]
#[Package('storefront')]
class RefundController extends StorefrontController
{
    public function __construct(
        private readonly CancelOrderRoute $cancelOrderRoute,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route(path: '/refund-order-button/cancel/{orderId}', name: 'frontend.refund.order.cancel', methods: ['POST'])]
    #[Since('6.6.0.0')]
    public function cancelOrder(string $orderId, Request $request, SalesChannelContext $context): Response
    {
        $cancelOrderRequestData = [
            'orderId' => $orderId,
            'transition' => 'cancel',
        ];

        $cancelOrderRequest = $request->duplicate(null, $cancelOrderRequestData);
        
        $this->cancelOrderRoute->cancel($cancelOrderRequest, $context);

        $this->addFlash('success', $this->trans('refund-order-button.cancelSuccess'));

        if ($context->getCustomer() && $context->getCustomer()->getGuest() === true) {
            return $this->redirectToRoute(
                'frontend.account.order.single.page',
                [
                    'deepLinkCode' => $request->get('deepLinkCode'),
                ]
            );
        }

        return $this->redirectToRoute('frontend.account.order.page');
    }
}
