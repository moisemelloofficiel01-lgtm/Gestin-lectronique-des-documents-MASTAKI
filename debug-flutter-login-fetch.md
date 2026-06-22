# [OPEN] Debug Session: flutter-login-fetch

## Contexte
- Symptome: Flutter affiche `ClientException: Failed to fetch` sur `http://localhost/team37/src/api/auth/login.php`.
- Attendu: le login Flutter doit fonctionner comme le login PHP du site.
- Zone suspecte: Flutter Web, reseau navigateur, CORS, URL runtime, reponse API.

## Hypotheses
- H1: Flutter Web n'atteint pas reellement `login.php` a cause d'un blocage navigateur lie a l'origine.
- H2: La requete part mais la reponse backend n'est pas exploitable par le client Flutter.
- H3: Le probleme vient d'une difference de comportement entre appel Web Flutter et appel web PHP classique.
- H4: L'URL appelee depuis Flutter n'est pas celle utilisee au runtime reel.
- H5: Une erreur JavaScript reseau complementaire existe dans le navigateur et n'apparait pas dans le message Flutter.

## Plan
1. Instrumenter Flutter pour tracer l'URL, le type d'erreur et le statut HTTP.
2. Instrumenter `login.php` pour tracer methode, origine, content-type et presence du payload.
3. Reproduire l'erreur.
4. Analyser les preuves.
5. Appliquer le correctif minimal.

## Etat
- Statut: en cours
- Correctif logique: non commence
- Instrumentation: a faire
