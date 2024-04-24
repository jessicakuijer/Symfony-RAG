<?php

namespace App\Controller;

use App\Form\ChatBotType;
use LLPhant\Chat\OpenAIChat;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use LLPhant\Query\SemanticSearch\QuestionAnswering;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use LLPhant\Embeddings\EmbeddingGenerator\OpenAIEmbeddingGenerator;
use LLPhant\Embeddings\VectorStores\FileSystem\FileSystemVectorStore;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ChatBotController extends AbstractController
{   
    private ParameterBagInterface $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;
    }
    
    #[Route('/', name: 'app_chatbot')]
    public function index(Request $request, SessionInterface $session): Response
    {
        if (!$session->get('isAuthenticated')) {
            return $this->redirectToRoute('password_prompt');
        }

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

    #[Route('/password-prompt', name: 'password_prompt')]
    public function passwordPrompt(Request $request, SessionInterface $session): Response
    {
        $password = $this->parameterBag->get('PASSWORD_PROMPT');
        if ($request->isMethod('POST')) {
            $enteredPassword = $request->request->get('password');
            $correctPassword = $password;

            if ($enteredPassword === $correctPassword) {
                $session->set('isAuthenticated', true);
                return $this->redirectToRoute('app_chatbot');
            } else {
                $this->addFlash('error', 'Mot de passe incorrect.');
            }
        }
        return $this->render('chatbot/passwordprompt.html.twig');
    }
}
