# Implémentation du Système de Chat Support

## Résumé des Changements

### 1. **Masquage du Bouton Chat pour Admin et Superviseur**
- **Fichier**: `templates/base.html.twig`
- **Changement**: Modification de la condition d'affichage du bouton de chat
- **Code**: 
  ```twig
  {% if app.user and not is_granted('ROLE_ADMIN') and not is_granted('ROLE_SUPERVISOR') %}
  ```
- Le bouton chat ne s'affiche maintenant que pour les utilisateurs réguliers (ROLE_USER)

### 2. **Système de Sauvegarde des Messages**
- **Fichier**: `templates/base.html.twig`
- **Fonction**: `sendSupportMessage()`
- **Changement**: Intégration d'une requête AJAX pour sauvegarder les messages dans la base de données
- **Endpoint API**: `POST /api/support/message/send`
- Les messages sont sauvegardés automatiquement quand l'utilisateur les envoie

### 3. **Contrôleur API Support**
- **Fichier**: `src/Controller/SupportController.php`
- **Endpoint**: `POST /api/support/message/send`
- Accepte les messages JSON avec champ `content`
- Crée une nouvelle entité `SupportMessage` avec l'utilisateur comme expéditeur

### 4. **Contrôleur Admin Messages**
- **Fichier**: `src/Controller/Admin/AdminMessagesController.php`
- **Route de base**: `/admin/messages/`
- **Endpoints disponibles**:
  - `GET /admin/messages/` - Liste toutes les conversations
  - `GET /admin/messages/client/{clientId}` - Affiche la conversation avec un client
  - `POST /admin/messages/reply/{clientId}` - Envoie une réponse à un client
  - `GET /admin/messages/api/messages/{clientId}` - API pour récupérer les messages (JSON)
  - `POST /admin/messages/mark-read/{messageId}` - Marque un message comme lu
- **Fonctionnalités**:
  - Groupement des messages par conversation (par client)
  - Affichage des informations du client (nom, email)
  - Suivi des messages lus/non lus
  - Interface de réponse direct aux clients

### 5. **Template Admin Messages**
- **Fichier**: `templates/admin/messages/index.html.twig`
- **Interface**:
  - Sidebar avec liste des conversations (triées par derniers messages)
  - Zone principale affichant la conversation sélectionnée
  - Champ de réponse en bas de la conversation
  - Auto-refresh toutes les 3 secondes pour les nouveaux messages
  - Affichage des informations client: nom, email, date/heure
  - Compteur de messages non lus par conversation

### 6. **Intégration Menu Navbar**
- **Fichier**: `templates/base.html.twig`
- **Changement**: Ajout d'un lien "📨 Messages" dans le menu dropdown pour Admin/Superviseur
- **Condition**: Visible uniquement pour `ROLE_ADMIN` ou `ROLE_SUPERVISOR`

## Structure de l'Entité SupportMessage

```php
SupportMessage {
    id: int (Primary Key)
    sender: User (ManyToOne) - Utilisateur qui envoie le message
    receiver: User? (ManyToOne) - Admin/Superviseur qui répond (nullable)
    content: text - Contenu du message
    isRead: bool - Statut de lecture
    createdAt: datetime - Date/heure d'envoi
}
```

## Flux de Communication

### Client → Admin
1. Client envoie un message via le chat popup
2. Message est sauvegardé via AJAX à `/api/support/message/send`
3. Message apparaît dans le chat client instantanément
4. Admin/Superviseur voit le message dans `/admin/messages/`

### Admin → Client
1. Admin répond à un message via `/admin/messages/`
2. Réponse est sauvegardée avec `receiver` = client
3. Client voit la réponse dans le chat popup (auto-refresh possible)

## Points Clés

- ✅ Chat popup masqué pour Admin/Superviseur
- ✅ Messages sauvegardés dans la BD
- ✅ Interface Admin pour gérer les conversations
- ✅ Réponses en temps quasi-réel (via auto-refresh)
- ✅ Gestion des messages lus/non lus
- ✅ Informations client complètes (nom, email, date)
- ✅ Menu intégré pour accéder aux messages

## Routes Requises

Les routes suivantes doivent être accessibles:
- `admin_messages_index` - Liste des messages (GET /admin/messages/)
- `admin_messages_client_conversation` - Conversation avec un client (GET /admin/messages/client/{clientId})
- `admin_messages_send_reply` - Envoyer une réponse (POST /admin/messages/reply/{clientId})
- `admin_messages_api_get_messages` - API messages (GET /admin/messages/api/messages/{clientId})
- `admin_dashboard` - Tableau de bord admin (doit exister)
- `api_support_send_message` - Envoyer message client (POST /api/support/message/send)

## Requête Admin/Superviseur Requise

Dans la configuration de sécurité Symfony, les routes `/admin/messages/*` doivent être protégées avec:
- `#[IsGranted('ROLE_ADMIN')]` pour Admin seulement
- Ou possiblement `#[IsGranted('ROLE_SUPERVISOR')]` si les superviseurs doivent y accéder

Status: ✅ Prêt pour utilisation
