![full_interhop_white_logopng (1)](https://user-images.githubusercontent.com/101267251/199488783-9cc034f0-bda5-4ee4-81d0-bad81e90eaf3.png)
![easyappointments-website-logo](https://user-images.githubusercontent.com/101267251/199488794-d3e9821b-903a-40b9-b1e0-2df1b7c536b6.png)

Démarrage du projet EasyAppointments

Prérequis au bon fonctionnement de l’application : 

MAMP: https://www.mamp.info/en/mamp/mac/

NodeJS (en version LTS – Long Time Support): <https://nodejs.org/en/download/>

-- Étape 1 : Récupérer le lien du fork EA.

![Aspose Words e95f119a-61ab-4b5f-9dfa-3b94f00b904e 003](https://user-images.githubusercontent.com/101267251/199488937-681a8dd3-7c6a-454d-aa77-78f12d113f06.png)

<https://framagit.org/interhop/toobib/easyappointments.git>

Cliquer sur le bouton Clone, copiez le lien « clone with HTTPS » puis passez sur votre IDE.

Dans ce cas, je vais utiliser PHPStorm mais la manipulation est la même pour IntelliJ.

-- Étape 2 : Cloner le repo sur votre IDE.

Ouvrez votre IDE et sur la page d’ouverture d’un projet, cliquez sur « **Get from VSC** ».

![Aspose Words e95f119a-61ab-4b5f-9dfa-3b94f00b904e 004](https://user-images.githubusercontent.com/101267251/199489341-9a2a13df-d158-4488-b8ce-e0f6884dfaf3.png)

Cette page vous permet d’importer votre projet.

![Aspose Words e95f119a-61ab-4b5f-9dfa-3b94f00b904e 005](https://user-images.githubusercontent.com/101267251/199489391-8a357ab6-fcdb-47c1-8679-48ce5eb94a8a.png)

1. Dans la partie URL, il vous suffit de coller le lien du fork que vous avez copier précédemment (voir capture ci-dessus).
1. Directory est le chemin d’accès vers le dossier dans lequel vous souhaitez installer votre projet. Par défaut, avec PHP, il faut enregistrer votre projet dans le dossier htdocs afin de pouvoir l’exécuter avec Mamp.
1. Cliquez sur « Clone » et laisser votre IDE initialiser le projet.

-- Étape 3 : Préparer le projet dans votre ide.

Lorsque votre ide aura terminé d’indexer le projet, vous arriverez sur cette page avec à gauche, la racine de votre projet et tous les fichiers existants.

<img width="1461" alt="Capture d’écran 2022-11-02 à 15 25 50" src="https://user-images.githubusercontent.com/101267251/199515484-f180eb47-6176-4d56-8349-ca5566448981.png">

Maintenant vous pouvez ouvrir le terminal de votre IDE.

Dans celui-ci nous allons commencer à entrer certaines lignes de code afin de finaliser l’installation.

Commencez par la commande d'installation NPM :

npm install 

<img width="1212" alt="Capture d’écran 2022-10-26 à 10 56 33" src="https://user-images.githubusercontent.com/101267251/199507528-f89a0077-b9e5-492f-82a1-95b7e0093ade.png">

Ensuite tapez la commande de mise à jour de Composer : 

composer update 

<img width="541" alt="Capture d’écran 2022-11-02 à 14 55 48" src="https://user-images.githubusercontent.com/101267251/199507904-ddf66311-dd84-443a-9753-a8c873993cdb.png">

Note : Si vous rencontre une erreur au moment de d’exécuter la commande « composer update », merci de recommencer avec la commande : 

Composer update –ignore-platform-regs

-- Étape 4 : Mise en place de l’outil de gestion des bdd.

Afin de faire fonctionner pleinement l’application, il vous faudra une base de données active et fonctionnel. Pour ce faire, sur mac vous avez la possibilité d’utiliser MAMP (lien disponible dans les prérequis).

Une fois celui-ci installé, rien de plus simple. 

Ouvre mamp puis cliquer sur Start en haut à droite.

<img width="541" alt="Capture d’écran 2022-10-26 à 11 12 55" src="https://user-images.githubusercontent.com/101267251/199508822-c13a61b0-ea10-4f41-95af-81ba6a1df4f6.png">

Une fois votre serveur mamp démarré, cliquez sur « WebStart » afin d’accéder à votre interface.

Une fois sur l’interface web de Mamp, cliquez sur « Tools » en haut à gauche puis sélectionnez « phpMyAdmin ».

<img width="866" alt="Capture d’écran 2022-10-26 à 11 13 12" src="https://user-images.githubusercontent.com/101267251/199509097-08146c0e-ee83-4ea2-96bb-b10e9ac6b493.png">

Vous voilà dans votre interface de gestion de vos BDD.

Prochaine étape, la création de la bdd.

-- Étape 5 : Création de la BDD.

Afin de pouvoir enregistrer des utilisateurs, des actions et autres, il nous faut une bdd.

Pour ce faire, cliquez sur « Nouvelle base de données » sur la gauche de votre page.

<img width="124" alt="Capture d’écran 2022-10-26 à 11 13 27" src="https://user-images.githubusercontent.com/101267251/199509502-a017d509-437c-4481-bba9-7ddb2670190c.png">

Ensuite nous allons créer une base de données pour EasyAppointments.

Sur l’écran suivant, il vous suffit de nommer votre bdd (le nom de celle-ci est à votre convenance) puis de cliqué sur « Créer ».

<img width="868" alt="Capture d’écran 2022-10-26 à 11 13 46" src="https://user-images.githubusercontent.com/101267251/199509567-74cfdfcd-a2e3-428b-b93a-18caedfb6b9d.png">

Voila ! Votre base de données est créée et prête à être utiliser.

-- Étape 6 : Ajout de la BDD à notre application.

De retour sur notre Ide, nous allons faire en sorte que celui-ci communique avec notre base de données.

Pour ce faire, ouvrez le fichier nommé « config-sample.php » qui se trouve à la racine de votre projet.

<img width="297" alt="Capture d’écran 2022-10-26 à 10 57 30" src="https://user-images.githubusercontent.com/101267251/199510139-69c478cc-7e98-40fc-b140-041fdacbae1d.png">

Les informations qui vont nous intéresser dans ce fichier se trouvent entre la ligne 33 et la ligne 45.

Par défaut, elle sont préremplie comme dans la capture ci-dessous.

<img width="1308" alt="Capture d’écran 2022-10-26 à 10 57 45" src="https://user-images.githubusercontent.com/101267251/199511467-72ce388a-24d3-4754-80e8-136efd3b60c7.png">

A nous d’entrer nos informations de connexion comme dans la capture suivante.

<img width="1305" alt="Capture d’écran 2022-10-26 à 10 58 30" src="https://user-images.githubusercontent.com/101267251/199511572-f6a13be0-a050-4ea1-bab7-1de5d51745ec.png">

« BASE\_URL » indique l’url à utiliser afin de se connecter à l’application. Par défaut celle-ci se nomme : <http://localhost/lenomdevotreprojet>

Ensuite nous allons paramétrer les informations de BDD.

« DB\_HOST » correspond au type de connexion. Sauf indication contraire, par défaut celui-ci est en localhost.

« DB\_NAME » est le nom que vous avez donnez à votre BDD dans phpMyAdmin.

« DB\_USERNAME » est le nom que vous avez donnez à votre utilisateur par défaut dans phpMyAdmin.

« DB\_PASSWORD » est le mot de passe que vous avez donnez à votre utilisateur par défaut dans phpMyAdmin .

Une fois cette étape terminée, il va falloir renommer le fichier que nous venons de modifier, config-sample.php en config.php.

Pour ce faire, clic droit sur le fichier puis « refactor » et ensuite rename. 

<img width="1316" alt="Capture d’écran 2022-10-26 à 11 00 29" src="https://user-images.githubusercontent.com/101267251/199510248-1a5d1848-49d3-40b9-a418-3931fe952f40.png">

Il ne vous reste qu’à modifier le nom de votre fichier comme sur la capture ci-dessous et cliquer sur « Refactor ».

<img width="352" alt="Capture d’écran 2022-10-26 à 11 01 13" src="https://user-images.githubusercontent.com/101267251/199512368-c389c76e-3003-46af-a039-430169ace7e3.png">

Cela va vous ouvrir un onglet dans le bas de votre ide et va vous demander si vous voulez faire un refactor. Cliquez sur « Do refactor » et c’est fini.

<img width="1706" alt="Capture d’écran 2022-10-26 à 11 01 28" src="https://user-images.githubusercontent.com/101267251/199512425-b08fa6a6-48a4-4519-81b1-ed1d6f5a7b70.png">

-- Étape 7 : Démarrage de notre application.

Viens l’étape finale. Démarrer notre projet.

Pour ce faire, taper la commande « npm start » dans le terminal de votre ide.

Vous devriez obtenir un résultat similaire à la capture ci-dessous.

<img width="1109" alt="Capture d’écran 2022-10-26 à 12 29 04" src="https://user-images.githubusercontent.com/101267251/199512601-11a2b7e3-becd-46aa-9034-a5a54f0f98e5.png">

Maintenant, il vous faut retourner sur mamp, cliquez « webstart » et une fois sur la page internet de votre mamp, cliquez sur « website » en haut à droite.

<img width="865" alt="Capture d’écran 2022-10-26 à 12 31 52" src="https://user-images.githubusercontent.com/101267251/199512999-852c388c-188b-4b8a-adac-a4284a8a07e4.png">

Vous arriverez sur une fenêtre « Index Of / » dans laquelle s’afficherons tous les projets que vous avez dans votre dossier htdocs.

<img width="324" alt="Capture d’écran 2022-11-02 à 15 19 04" src="https://user-images.githubusercontent.com/101267251/199513876-2d0f3516-594b-4fb9-bd23-29b681617a85.png">

Il vous suffit de cliquer sur celle que vous souhaitez lancer, dans notre cas easyappointments et si toutes les étapes ont bien été respectée, vous devriez arriver sur la page d’accueil de votre application.

![MicrosoftTeams-image (1)](https://user-images.githubusercontent.com/101267251/206697695-98281f36-924f-4b99-80c7-e7fdb8a67a90.png)

Bienvenue sur Easyappointments, bon dev à vous :)
