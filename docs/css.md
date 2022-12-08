![full_interhop_white_logopng](https://user-images.githubusercontent.com/101267251/206343112-53601666-49b2-4180-a293-749080148945.png)
![easyappointments-website-logo](https://user-images.githubusercontent.com/101267251/206343106-8a8913c2-6ecf-44f3-bd1e-1e4a1cfa646a.png)

# Comment modifier le CSS de votre application ?

Ce tutoriel vous servira à modifier le CSS (couleurs et mises en pages) de votre application  **EasyAppointments**. Pour cela, il vous suffit de suivre les étapes suivantes et de laisser parler votre **créativité.** 

# Les fichiers CSS

Les fichiers **CSS**, qui vous permettront de modifier votre application, se trouvent dans le dossier suivant : 

lenomdevotreprojet/assets/css

À l'intérieur de ce dossier se trouvent plusieurs fichiers **xxx .css** :

<img width="407" alt="Capture d’écran 2022-12-08 à 04 28 02" src="https://user-images.githubusercontent.com/101267251/206349587-1d7353d9-cf36-4327-bd30-1d64aa8f446f.png">

1. **backend.css** -> qui permet de gérer le css du backend de l'application.
2. **error404.css** -> qui permet de gérer le css du message d'erreur 404.
3. **forgot_password.css** -> qui permet de gérer le css de la page "mot de passe oublié".
4. **frontend.css** -> qui permet de gérer le css de la partie frontend de l'application.
5. **general.css** -> qui permet de gérer le css des parties communes de l'application (header, footer, navbar).
6. **installation.css** -> qui permet de gérer le css de la page d'installation du projet (celle qui permet de créer le premier utilisateur)
7. **login.css** -> qui permet de gérer le css de la page login de l'application.
8. **logout.css** -> qui permet de gérer le css de la page de déconnexion de l'application.
9. **no_privileges.css** -> qui permet de gérer la partie css de la page "privilèges d'un utilisateur".
10. **update.css** -> qui permet de gérer la partie css de la page de mise à jour de l'application

## Comment modifier les couleurs de l'application ?

Il y a plusieurs façons de modifier les couleurs et la mise en page de l'application, mais la plus simple et la plus rapide reste la suivante : 

**Ouvrir l'application** dans votre navigateur et faites un clic droit sur la partie que vous souhaitez modifier.

<img width="1728" alt="Capture d’écran 2022-12-08 à 03 57 37" src="https://user-images.githubusercontent.com/101267251/206349699-b6e41b90-23b4-4b8f-843c-c57e0a19b047.png">

Sélectionner **"inspecter"** et vous verrez un onglet s'ouvrir à droite de votre navigateur.

<img width="1728" alt="Capture d’écran 2022-12-08 à 03 57 48" src="https://user-images.githubusercontent.com/101267251/206349774-6440bcc6-df1f-48ca-85d8-6a739f47631c.png">

Celui-ci vous permet, dans notre cas, de pouvoir **lire le code de la page** et d'y retrouver l'id de l'objet que vous souhaitez modifier. 
L'avantage de cette méthode, c'est qu'elle vous **permet de tester les modifications avant de les appliquer dans votre IDE**. Il vous suffit de modifier la couleur directement dans votre navigateur et une fois la couleur définitive choisie, **la retranscrire dans le fichier CSS**. Le fonctionnement reste le même pour la **mise en page, les paddings, tailles, display, les polices d'écriture, etc, etc**.

<img width="342" alt="Capture d’écran 2022-12-08 à 03 57 59" src="https://user-images.githubusercontent.com/101267251/206350028-c713fe95-e118-4bd5-968f-8695d7ea63c7.png">

Une fois que vous avez l'id de l'objet que vous recherchez et que vous avez fait votre choix sur les modifications à effectuer, il ne vous reste plus qu'à vous diriger vers votre **IDE, lancer l'application et rechercher, dans le dossier CSS, le fichier correspondant à la page que vous souhaitez modifier**.
Dans notre cas, nous allons ouvrir le fichier **"frontend.css"**.
À l'intérieur de celui-ci, il va falloir trouver l'id de ce que nous cherchons. En l'occurrence :
**#book-appointment-wizzard #header** dans lequel vous pourrez modifier le **"background"**.

<img width="410" alt="Capture d’écran 2022-12-08 à 04 33 57" src="https://user-images.githubusercontent.com/101267251/206350436-f7d1f3ce-441d-46de-ad04-2d7c1d2bcee5.png">

En **CSS** il existe plusieurs méthodes pour **modifier une couleur** :

1. color : crimson; 
2. color : rgb(255,0,0); 
3. color : hsl(16,100%,50%); 
4. color : #FF00FF;

Vous trouverez ici toutes **les couleurs utilisables** :

https://htmlcolorcodes.com/fr/

À vous de faire **votre choix**.

## Dernière étape de la modification.

Une fois que vous aurez fait vos **modifications**, il se peut que celles-ci ne soient pas prises en compte immédiatement.
**Deux solutions :** 

1. Soit, elles sont **prises en compte immédiatement** et dans ce cas, c'est la fin de ce tutoriel.
2. 
3. Soit, elles ne sont **pas prises en compte** et dans ce cas-là, vous avez juste à faire un **"run build"** dans le terminal de votre ide afin que l'appli compile les fichiers et prenne en compte les modifications.
