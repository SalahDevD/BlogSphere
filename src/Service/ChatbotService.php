<?php

namespace App\Service;

class ChatbotService
{
    /**
     * Base de connaissances du chatbot avec mots-clés et réponses
     */
    private array $knowledgeBase = [
        // Bienvenue et salutations
        [
            'keywords' => ['bonjour', 'bonsoir', 'salut', 'hello', 'hi'],
            'response' => '👋 Bonjour ! Bienvenue sur BlogSphere. Comment puis-je vous aider aujourd\'hui ?'
        ],
        // Création d\'article
        [
            'keywords' => ['créer', 'article', 'publier', 'nouveau', 'écrire'],
            'response' => '📝 Pour créer un article :
1. Accédez à votre profil
2. Cliquez sur "Nouvel article"
3. Remplissez le titre, catégorie et contenu
4. Votre premier article sera soumis pour approbation
5. Une fois approuvé, vous pourrez publier directement

Besoin de plus de détails ?'
        ],
        // Validation des articles
        [
            'keywords' => ['validation', 'approbation', 'approuvé', 'en attente', 'pending'],
            'response' => '✅ Système de validation des articles :
- **Premier article** : Doit être validé par un superviseur
- **Articles suivants** : Publiés automatiquement après approbation du premier

Vous pouvez suivre l\'état de vos articles dans votre profil.'
        ],
        // Commentaires
        [
            'keywords' => ['commentaire', 'répondre', 'discussion', 'comment'],
            'response' => '💬 À propos des commentaires :
- Commentez les articles pour participer aux discussions
- Réagissez avec des likes/dislikes
- Les commentaires peuvent être supprimés s\'ils violent nos règles'
        ],
        // Réactions
        [
            'keywords' => ['réaction', 'like', 'dislike', 'emoji', 'émoticônes'],
            'response' => '👍 Système de réactions :
- Réagissez aux articles et commentaires
- Utilisez les likes pour montrer votre appréciation
- Les réactions aident à identifier le contenu populaire'
        ],
        // Signalement
        [
            'keywords' => ['signaler', 'rapport', 'abus', 'contenu inapproprié', 'problème'],
            'response' => '🚩 Signaler du contenu :
1. Cliquez sur le bouton "Signaler" sur l\'article ou le commentaire
2. Sélectionnez la raison du signalement
3. Notre équipe superviseur examinera le contenu
4. Les actions appropriées seront prises

Merci de nous aider à maintenir une communauté saine !'
        ],
        // Profil
        [
            'keywords' => ['profil', 'compte', 'paramètres', 'photo', 'image'],
            'response' => '👤 Gestion du profil :
- Accédez à votre profil via le menu utilisateur
- Modifiez votre photo de profil
- Visualisez tous vos articles
- Consultez vos statistiques (likes, commentaires)'
        ],
        // Catégories
        [
            'keywords' => ['catégorie', 'tags', 'filtrer', 'rechercher', 'thème'],
            'response' => '🏷️ Organisation du contenu :
- Les articles sont organisés par catégories
- Utilisez les tags pour trouver des contenus spécifiques
- Filtrez les articles par thème qui vous intéresse'
        ],
        // Contact/Support
        [
            'keywords' => ['contact', 'support', 'email', 'aide', 'problème', 'erreur'],
            'response' => '📧 Besoin d\'aide supplémentaire ?
- Consultez notre FAQ
- Utilisez le formulaire de contact pour des questions spécifiques
- Notre équipe vous répondra dans les 24-48 heures

Y a-t-il quelque chose de spécifique que je puisse vous aider ?'
        ],
        // Sécurité
        [
            'keywords' => ['mot de passe', 'sécurité', 'connexion', 'authentification', 'login'],
            'response' => '🔐 Sécurité et authentification :
- Changez régulièrement votre mot de passe
- Utilisez un mot de passe fort (majuscules, minuscules, chiffres, symboles)
- Ne partagez pas vos identifiants
- Déconnectez-vous sur les appareils publics'
        ],
        // Supprimer un article
        [
            'keywords' => ['supprimer', 'effacer', 'retirer', 'delete', 'suppression'],
            'response' => '🗑️ Suppression de contenu :
- Vous pouvez supprimer vos propres articles
- Les superviseurs peuvent supprimer du contenu signalé
- La suppression est définitive et irréversible
- Réfléchissez bien avant de supprimer'
        ],
        // Règles communautaires
        [
            'keywords' => ['règles', 'conditions', 'utilisation', 'cgu', 'terme', 'respect'],
            'response' => '📋 Règles communautaires :
- Respectez les autres utilisateurs
- Pas de contenu offensant, haineux ou illégal
- Pas de spam ou de promotion non autorisée
- Respectez les droits d\'auteur
- Le contenu violant ces règles sera supprimé'
        ],
        // Performance/Bug
        [
            'keywords' => ['lent', 'rapide', 'bug', 'erreur', 'problème technique', 'crash'],
            'response' => '⚙️ Problèmes techniques :
- Essayez de rafraîchir la page (F5)
- Videz le cache de votre navigateur
- Essayez un autre navigateur
- Si le problème persiste, signalez-le au support'
        ],
    ];

    /**
     * Générer une réponse du chatbot basée sur la question de l'utilisateur
     */
    public function generateResponse(string $userMessage): string
    {
        $userMessage = strtolower(trim($userMessage));

        // Analyser la question et trouver la meilleure correspondance
        $bestMatch = null;
        $bestScore = 0;

        foreach ($this->knowledgeBase as $item) {
            foreach ($item['keywords'] as $keyword) {
                if (strpos($userMessage, $keyword) !== false) {
                    $score = 1;
                    // Augmenter le score si le mot-clé est au début
                    if (strpos($userMessage, $keyword) === 0) {
                        $score += 0.5;
                    }
                    
                    if ($score > $bestScore) {
                        $bestScore = $score;
                        $bestMatch = $item['response'];
                    }
                }
            }
        }

        // Si une correspondance a été trouvée, retourner la réponse
        if ($bestMatch) {
            return $bestMatch;
        }

        // Réponse par défaut si aucune correspondance
        return '🤔 Je n\'ai pas bien compris votre question. Essayez de me demander :
- Comment créer un article
- Comment fonctionne la validation
- Comment signaler du contenu
- Les règles communautaires
- Conseils de sécurité
- Ou contactez notre équipe pour plus d\'aide ✉️';
    }
}
