# 💊 Projet personnel : TimePills

📅 **Années** : 2024 - 2025
🛠️ **Type** : Projet personnel (Web)
🧰 **Technologies principales** : Symfony, Twig, CalendarJS

---

## 📊 En quelques chiffres

| 🚀 Éléments             | 📌 Détails                         |
| ----------------------- | ---------------------------------- |
| ⏱️ Temps passé          | 50+ heures                         |
| 👨‍💻 Réalisé par       | Projet personnel (réalisé seul)    |
| 🧑‍💻 Langages utilisés | PHP, CSS3, JavaScript, HTML5, Twig |
| 🔧 Framework principal  | Symfony                            |

---

## 🧠 Contexte du projet

**TimePills** est un outil en ligne visant à faciliter la prise de traitements médicaux. Il permet de :

* Créer des plannings personnalisés de prise de médicaments.
* Gérer les jours de prise et de pause (ex. : pilules contraceptives).
* Visualiser les traitements sur un calendrier interactif.
* Recevoir des rappels par email pour ne pas oublier les prises.

📝 L'idée du projet m'est venue en voyant ma copine noter ses prises sur un cahier papier. J'ai souhaité lui proposer une solution numérique simple et accessible.

---

## 🧑‍💻 Ma contribution

* 💡 Conception de l'idée, du cahier des charges et du design (UI avec Figma).
* 🧱 Développement complet de l'application web.
* 📅 Intégration d’un calendrier dynamique pour visualiser les traitements.
* 📬 Développement d’un système de rappels automatisés par email via des **commandes Symfony** et **CronTasks**.
* ☁️ Déploiement sur un hébergeur (Hostinger).

La gestion des notifications fut la partie la plus délicate :

* Création d'une commande PHP Symfony pour envoyer les emails.
* Mise en place d'une **CronTask** planifiée toutes les minutes pour exécuter cette commande automatiquement.

---

## 🛠️ Architecture technique

```text
📁 TimePills
├── 📦 Backend (Symfony)
│   ├── Command : Envoi de rappels
│   ├── Controllers : Gestion des utilisateurs, plannings, traitements
│   ├── Entities : Utilisateurs, Traitements, Notifications
│   └── Services : Calcul des jours de pause / rappel
├── 🎨 Frontend (Twig, CSS, JS)
│   ├── Pages Twig avec composants interactifs
│   └── Intégration de CalendarJS pour l'affichage visuel
├── ⚙️ CronTask
│   └── Exécution automatique de la commande de rappel toutes les minutes
└── ☁️ Hébergement : Hostinger
```

---

## 📆 Phases de réalisation

1. 📄 **Phase 0** : Construction du cahier des charges
2. 🎨 **Phase 1** : Maquettage de l’UI sur Figma
3. 🧱 **Phase 2** : Développement de la base du projet (Symfony + Twig)
4. 📅 **Phase 3** : Ajout du calendrier interactif
5. ⚙️ **Phase 4** : Création de la commande PHP & CronTask
6. 📬 **Phase 5** : Implémentation des rappels par email

---

## 🎯 Lien avec les compétences du BUT Informatique

| 🎓 Compétences acquises | 📌 Détails                                           |
| ----------------------- | ---------------------------------------------------- |
| Développement Web       | Symfony, Twig, front-end & back-end                  |
| Frameworks avancés      | Utilisation poussée de Symfony                       |
| Automatisation          | CronTasks, commandes Symfony                         |
| Analyse & Algorithmie   | Calculs des jours de pause, logiques conditionnelles |
| Conception complète     | Du besoin utilisateur à l’application déployée       |

---

## 🧰 Outils utilisés

| 🔧 Outil  | 📋 Usage                                  |
| --------- | ----------------------------------------- |
| GitHub    | Versioning et gestion du code             |
| PhpStorm  | Environnement de développement            |
| Hostinger | Hébergement web + gestion des tâches CRON |

---

## 🔗 À venir

Le projet est toujours en cours de développement. Une fois stabilisé, je compte le publier en ligne afin qu'il soit accessible au plus grand nombre.
