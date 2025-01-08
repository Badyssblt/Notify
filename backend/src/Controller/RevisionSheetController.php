<?php

namespace App\Controller;

use App\Entity\RevisionSheet;
use Doctrine\ORM\EntityManagerInterface;
use Smalot\PdfParser\Parser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
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

    #[IsGranted('ROLE_USER')]
    #[Route('/api/revision/upload', name: 'app_revision_sheet_upload', methods: ['POST'])]
    public function uploadPdf(Request $request, EntityManagerInterface $manager): Response
    {
        $uploadedFile = $request->files->get('file');

        if ($uploadedFile && $uploadedFile->isValid()) {
            $filePath = $uploadedFile->getPathname();

            $text = $this->convertPdfToText($filePath);

            $title = $request->getPayload()->get('title');

            $revisionText = $this->generateRevisionSheetFromGroq($text);

            $revision = new RevisionSheet();

            $revision->setTitle($title);

            $revision->setContent($text);

            $revision->setOwner($this->getUser());

            $manager->persist($revision);
            $manager->flush();

            return $this->json(['message' => 'La note a bien été créer', 'id' => $revision->getId()], Response::HTTP_CREATED);
        }

        return new Response('Aucun fichier valide fourni.', Response::HTTP_BAD_REQUEST);
    }

    private function convertPdfToText(string $filePath): string
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);

        return $pdf->getText();
    }

    private function generateRevisionSheetFromGroq(string $text): string
    {
        // URL de l'API Groq
        $apiUrl = 'https://api.groq.com/openai/v1/chat/completions';


        $payload = [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => "Voici le texte extrait du PDF :\n$text\n Crée une fiche de révision formatée en **gras** pour les points importants, avec des *italiques* pour les exemples et des retours à la ligne pour chaque section."
                ]
            ]
        ];

        $response = $this->httpClient->request('POST', $apiUrl, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $_ENV['GROQ_API'],
            ],
            'json' => $payload,
        ]);

        $responseData = $response->toArray();

        return $responseData['choices'][0]['message']['content'] ?? 'Fiche de révision non disponible.';
    }





}
