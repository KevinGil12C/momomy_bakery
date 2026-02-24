<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Service to generate PDF documents from HTML
 */
class PdfService
{
    private $dompdf;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $this->dompdf = new Dompdf($options);
    }

    /**
     * Generate PDF and return as string
     */
    public function generate($html, $paper = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation);
        $this->dompdf->render();
        return $this->dompdf->output();
    }

    /**
     * Stream PDF direct to browser
     */
    public function stream($html, $filename = 'document.pdf', $paper = 'A4', $orientation = 'portrait')
    {
        $this->dompdf->loadHtml($html);
        $this->dompdf->setPaper($paper, $orientation);
        $this->dompdf->render();
        $this->dompdf->stream($filename, ["Attachment" => false]);
    }

    /**
     * Save PDF to server
     */
    public function save($html, $path)
    {
        $output = $this->generate($html);
        return file_put_contents($path, $output);
    }
}
