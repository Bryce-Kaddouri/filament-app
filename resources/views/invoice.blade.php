<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        /* Add your CSS styling here */
        body {
            padding: 0;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .invoice-box {
            /* max-width: 800px;
            margin: auto;
            padding: 30px; */
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
        }
        .invoice-box table td {
            padding: 5px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top table td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.information table td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #eee;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td:nth-child(2) {
            border-top: 2px solid #eee;
            font-weight: bold;
        }
        .absolute {
            position: absolute;
        }
        .border {
            border: 1px solid red;
        }
        .border-red-500 {
            border: 1px solid red;
        }
    </style>
</head>
<body>
    {{-- loop through the pages to  --}}
    @for ($i = 0; $i < $document->getPages()->count(); $i++)
        <div class="invoice-box">
            <img id="invoice-image-{{$i }}" style="" src="{{ 'data:image/png;base64,' . base64_encode($document->getPages()[$i]->getImage()->getContent()) }}" alt="Invoice Image" loading="lazy" width="{{ $document->getPages()[$i]->getImage()->getWidth() }}" height="{{ $document->getPages()[$i]->getImage()->getHeight() }}">
        </div>
    @endfor

    @foreach ($document->getEntities() as $entity)
        @if ($entity->getPageAnchor())
            @foreach ($entity->getPageAnchor()->getPageRefs() as $pageRef)
                @if ($pageRef->getBoundingPoly())
                    @php
                        $imgWidth = $document->getPages()[0]->getImage()->getWidth();
                        $imgHeight = $document->getPages()[0]->getImage()->getHeight();    
                        $vertices = $pageRef->getBoundingPoly()->getNormalizedVertices();
                        $x1 = $vertices[0]->getX() * $imgWidth;
                        $y1 = $vertices[0]->getY() * $imgHeight;
                        $x2 = $vertices[1]->getX() * $imgWidth;
                        $y2 = $vertices[2]->getY() * $imgHeight;
                    @endphp
                    <div class="absolute border border-red-500" style="left: {{ $x1 }}px; top: {{ $y1 }}px; width: {{ $x2 - $x1 }}px; height: {{ $y2 - $y1 }}px;"></div>
                @endif
            @endforeach
        @endif
    @endforeach


    {{-- <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <!-- Your company logo here -->
                                <img src="https://via.placeholder.com/150" alt="Company Logo" style="width:100%; max-width:300px;">
                            </td>
                            <td>
                                Invoice #: {{ $invoice['number'] ?? '123456' }}<br>
                                Created: {{ $invoice['date'] }}<br>
                                Due: <!-- Add due date here -->
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <!-- Your company information here -->
                                Your Company Name<br>
                                Street Address<br>
                                City, State, ZIP Code
                            </td>
                            <td>
                                <!-- Billing address -->
                                {{ $invoice['billing_address'] }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr class="heading">
                <td>Description</td>
                <td>Amount</td>
            </tr>
            @foreach ($invoice['line_items'] as $item)
                <tr class="item">
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['total'] }}</td>
                </tr>
            @endforeach
            <tr class="total">
                <td></td>
                <td>
                   Total: <!-- Add total amount here -->
                </td>
            </tr>
        </table>
    </div> --}}
</body>
</html>
