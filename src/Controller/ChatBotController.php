<?php

namespace App\Controller;

use App\Form\ChatBotType;
use LLPhant\Chat\OpenAIChat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;

class ChatBotController extends AbstractController
{   
    #[Route('/', name: 'app_chatbot')]
    public function index(Request $request): Response
    {

        $form = $this->createForm(ChatBotType::class);
        $form->handleRequest($request);
        $answer = ''; // On initialise la variable $answer à vide
        
        if ($form->isSubmitted() && $form->isValid()) {
            $question = $form->getData()['question'];

            $vectorStore = new FileSystemVectorStore('../documents-vectorStore.json');
            $embeddingGenerator = new OpenAIEmbeddingGenerator();

            $qa = new QuestionAnswering(
                $vectorStore,
                $embeddingGenerator,
                new OpenAIChat()
            );

            $answer = $qa->answerQuestion($question);
            $answer = htmlspecialchars($answer, ENT_QUOTES, 'UTF-8');
            $answer = nl2br($answer);

            //dd($answer);

            return $this->render('chatbot/index.html.twig', [
                'form' => $form,
                'answer' => $answer,
            ]);
        }

        return $this->render('chatbot/index.html.twig', [
            'form' => $form,
            'answer' => $answer,
        ]);
    }
}
