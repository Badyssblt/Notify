<?php

namespace App\Controller;

use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RevisionSheetController extends AbstractController
{


    public function __construct(private HttpClientInterface $httpClient)
    {
    }

    #[Route('/revision/sheet', name: 'app_revision_sheet')]
    public function index(): Response
    {
        return $this->render('revision_sheet/index.html.twig', [
            'controller_name' => 'RevisionSheetController',
        ]);
    }

    #[Route('/api/revision/upload', name: 'app_revision_sheet_upload', methods: ['POST'])]
    public function uploadPdf(Request $request): Response
    {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile && $uploadedFile->isValid()) {
            $filePath = $uploadedFile->getPathname();

            $text = $this->convertPdfToText($filePath);

        }

        return new Response('Aucun fichier valide fourni.', Response::HTTP_BAD_REQUEST);
    }

    private function convertPdfToText(string $filePath): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }





}
