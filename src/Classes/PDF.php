<?php

//This file is part of eavio/invoices.

namespace eavio\invoices\Classes;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\View;

//This is the PDF class.
class PDF
{
    /**
     * Generate the PDF.
     *
     * @method generate
     *
     * @param Eavio\Invoices\Classes\Invoice  $invoice
     * @param string                          $template
     *
     * @return Dompdf\Dompdf
     */
    public static function generate(Invoice $invoice, $template = 'default')
    {
        $template = strtolower($template);

        $options = new Options();

        $options->set('isRemoteEnabled', true);
        $options->set('isPhpEnabled', true);

        $pdf = new Dompdf($options);

        $context = stream_context_create([
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
                'allow_self_signed'=> true,
            ],
        ]);

        $pdf->setHttpContext($context);

        $GLOBALS['with_pagination'] = $invoice->with_pagination;

        $pdf->loadHtml(View::make('invoices::'.$template, ['invoice' => $invoice]));
        $pdf->render();

        return $pdf;
    }
}
