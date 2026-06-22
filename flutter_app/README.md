# flutter_app

Projet Flutter mobile/web pour le backend PHP du dossier `team37`.

## Lancement mobile

```bash
flutter run
```

## Lancement web

Si `flutter run -d chrome` echoue avec une erreur `canvaskit.js`, lance plutot :

```bash
flutter run -d chrome --wasm
```

Le projet contient un `web/flutter_bootstrap.js` personnalise qui prefere `skwasm`
quand il est disponible.

## Backend local

- Web local : `http://localhost/team37`
- API PHP : `http://localhost/team37/src`

## Remarque Android Emulator

Pour Android Emulator, l'application utilise `http://10.0.2.2/team37/src`
afin d'atteindre le `localhost` de Windows.
