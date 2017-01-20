@extends('layouts.app')

@section('title')
    - Print Invoice
@endsection

@section('styles')
    <style>
        .invoice-title h2, .invoice-title h3 {
            display: inline-block;
        }

        .table > tbody > tr > .no-line {
            border-top: none;
        }

        .table > thead > tr > .no-line {
            border-bottom: none;
        }

        .table > tbody > tr > .thick-line {
            border-top: 2px solid;
        }
    </style>
@endsection

@section('content')
    @parent
    <div class="row hidden-print">
        <div class="col-xs-12">
            <div class="panel">
                <div class="panel-body">
                    <a class="btn btn-primary" href="{{ route('sales.do_print', $sale->id) }}">
                        <i class="fa fa-print"></i>
                        Print Invoice
                    </a>
                    <a href="{{ route('sales.create') }}" class="btn btn-default">
                        <i class="fa fa-plus"></i>
                        New Sale
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="invoice-title">
                <h2>Invoice</h2><h3 class="pull-right">Order # {{ $sale->id }}</h3>
            </div>
            <hr>
            <div class="row">
                <div class="col-xs-6">
                    <address>
                        <strong>Billed To:</strong><br>
                        {{ $sale->customer->name }}<br>
                        {{ $sale->customer->address }}<br>
                    </address>
                </div>
                @if($sale->is_delivery)
                    <div class="col-xs-6 text-right">
                        <address><strong>Delivery</strong></address>
                    </div>
                @endif
            </div>
            <div class="row">
                <div class="col-xs-6">
                    <address>
                        <strong>Payment Method:</strong><br>
                        @if($sale->isPaid())
                            @if($payment->payment_method === 'CASH')
                                Cash
                            @else
                                Credit Card ending **** {{ substr($payment->card_number, -4) }}<br>
                            @endif
                        @else
                            Cash on delivery
                        @endif
                    </address>
                </div>
                <div class="col-xs-6 text-right">
                    <address>
                        <strong>Order Date & Time:</strong><br>
                        {{ $sale->opened_at->toFormattedDateString().' '.$sale->opened_at->toTimeString() }}<br><br>
                    </address>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title"><strong>Order summary</strong></h3>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-condensed">
                            <thead>
                                <tr>
                                    <td><strong>Item</strong></td>
                                    <td class="text-center"><strong>Price</strong></td>
                                    <td class="text-center"><strong>Quantity</strong></td>
                                    <td class="text-right"><strong>Totals</strong></td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->packages as $salePackage)
                                    <tr>
                                        <td>{{ $salePackage->package->name }}</td>
                                        <td class="text-center">{{ number_format($salePackage->price) }}</td>
                                        <td class="text-center">{{ number_format($salePackage->quantity) }}</td>
                                        <td class="text-right">{{ number_format($salePackage->calculateSubTotal()) }}</td>
                                    </tr>
                                @endforeach
                                @foreach($sale->items as $item)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td class="text-center">{{ number_format($item->price) }}</td>
                                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                                        <td class="text-right">{{ number_format($item->calculateSubTotal()) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <td class="thick-line"></td>
                                    <td class="thick-line"></td>
                                    <td class="thick-line text-right"><strong>Subtotal</strong></td>
                                    <td class="thick-line text-right">{{ number_format($sale->calculateSubTotal()) }}</td>
                                </tr>
                                @if($sale->customer_discount)
                                    <tr>
                                        <td class="no-line"></td>
                                        <td class="no-line"></td>
                                        <td class="text-right">
                                            <strong>Customer Discount ({{ number_format($sale->customer_discount).'%' }})</strong>
                                        </td>
                                        <td class="text-right">{{  number_format($sale->calculateAfterCustomerDiscount()) }}</td>
                                    </tr>
                                @endif
                                @if($sale->sales_discount)
                                    <tr>
                                        <td class="no-line"></td>
                                        <td class="no-line"></td>
                                        <td class="text-right">
                                            <strong>Sale Discount ({{ number_format($sale->sales_discount).'%' }})</strong>
                                        </td>
                                        <td class="text-right">{{  number_format($sale->calculateAfterSalesDiscount()) }}</td>
                                    </tr>
                                @endif
                                @if($sale->isPaid())
                                    @if($payment->payment_method === 'CREDIT_CARD')
                                        <tr>
                                            <td class="no-line"></td>
                                            <td class="no-line"></td>
                                            <td class="text-right">
                                                <strong>Credit Card Tax ({{ number_format($payment->card_tax).'%' }})</strong>
                                            </td>
                                            <td class="text-right">{{ number_format($payment->calculateTotal()) }}</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td class="no-line"></td>
                                        <td class="no-line"></td>
                                        <td class="text-right"><strong>Total</strong></td>
                                        <td class="text-right">{{ number_format($payment->calculateTotal()) }}</td>
                                    </tr>
                                @else
                                    <tr>
                                        <td class="no-line"></td>
                                        <td class="no-line"></td>
                                        <td class="text-right"><strong>Total</strong></td>
                                        <td class="text-right">{{ number_format($sale->calculateTotal()) }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @if(Session::get('doPrint'))
        <script type="text/javascript">
            window.location = "{{ route('sales.do_print', $sale->id) }}";
        </script>
    @endif
@endsection