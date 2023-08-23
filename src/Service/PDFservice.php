<?php

namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;

class PDFservice
{
    private $domPDF;
    public FUNCTION __construct(){
        $this->domPDF = NEW Dompdf();
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Garamond');
        $this->domPDF->setOptions($pdfOptions);
    }

    public function showPDF($html){
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->stream("details.pdf",[
            'Attachement' => false
        ]);
    }
    public function generateBinairyPDF($html){
        $this->domPDF->loadHtml($html);
        $this->domPDF->render();
        $this->domPDF->output();
    }
}