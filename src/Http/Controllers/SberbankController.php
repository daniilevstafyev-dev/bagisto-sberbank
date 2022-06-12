<?php

namespace Goldmangroup\Sberbank\Http\Controllers;

use Illuminate\Http\Request;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;

class SberbankController extends Controller
{

    public function __construct(
        protected OrderRepository $orderRepository,
        protected InvoiceRepository $invoiceRepository
    )
    {
    }

    protected function prepareInvoiceData($order)
    {
        $invoiceData = ["order_id" => $order->id,];

        foreach ($order->items as $item) {
            $invoiceData['invoice']['items'][$item->id] = $item->qty_to_invoice;
        }

        return $invoiceData;
    }


    public function fail($id)
    {

        $order = $this->orderRepository->getOrder($id);
        $this->orderRepository->cancel($order->id);

        session()->flash('error', __('shop::app.common.error'));


        return redirect()->route('shop.checkout.cart.index');
    }


    public function success(Request $request, $id)
    {
        $parameters = $request->all();

        if (isset($parameters['orderId'])) {

            $order = $this->orderRepository->getOrder($id);
            $this->orderRepository->update(['status' => 'processing'], $order->id);

            if ($order->canInvoice()) {
                $this->invoiceRepository->create($this->prepareInvoiceData($order));
            }

            session()->flash('order', $order);


            return redirect()->route('shop.checkout.success');

        }
    }
}