# Currency Converter API

[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D7.4-blue.svg)](https://php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![API Status](https://img.shields.io/badge/API-XE.com-green.svg)](https://xe.com)

Une API PHP simple et efficace pour la conversion de devises basée sur les taux de change de XE.com, avec l'Euro comme devise de référence.

## 🚀 Fonctionnalités

- **Conversion de devises** : Convertit entre différentes devises avec des taux en temps réel
- **Taux basés sur l'Euro** : Utilise l'Euro comme devise de référence pour tous les calculs
- **Cache intelligent** : Met en cache les taux pendant 2 heures pour optimiser les performances
- **API RESTful** : Interface simple via requêtes POST
- **Support multi-devises** : Support de plus de 170 devises mondiales

## 📁 Structure du projet

```
currency/
├── classes/
│   └── xe.php          # Classe principale pour la gestion des taux de change
├── public/
│   └── call.php        # Point d'entrée de l'API
├── Currency.postman_collection.json  # Collection Postman pour les tests
└── README.md           # Ce fichier
```

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Installation](#-installation)
- [Utilisation](#-utilisation)
- [Tests avec Postman](#-tests-avec-postman)
- [Logique de conversion](#-logique-de-conversion)
- [Optimisations](#-optimisations)
- [Limitations](#-limitations)
- [Maintenance](#-maintenance)
- [Développement](#-développement)
- [Contribution](#-contribution)
- [Licence](#-licence)

## 🛠 Installation

### Prérequis

- PHP 7.4 ou supérieur
- Serveur web (Apache, Nginx, Laragon, XAMPP, etc.)
- Extension PHP `json`
- Accès Internet pour récupérer les taux de change

### Configuration

1. **Cloner ou télécharger le projet** dans votre répertoire web :
   ```bash
   git clone https://github.com/votre-username/currency-converter.git currency
   cd currency
   ```

2. **Configurer votre serveur web** pour pointer vers le dossier du projet

3. **Tester l'installation** :
   - Accédez à `http://votre-domaine/currency/public/call.php`
   - Vous devriez voir une erreur JSON indiquant les paramètres manquants

## 🔧 Utilisation

### Point d'entrée API

**URL** : `POST /public/call.php`

### Paramètres requis

- `from_currency` : Code de la devise source (ex: "CAD", "USD", "GBP")
- `to_currency` : Code de la devise cible (ex: "EUR", "USD", "JPY")
- `method` : Méthode à utiliser (`getConversion` ou `getRates`)

### Méthodes disponibles

#### 1. `getConversion` - Conversion entre deux devises

**Exemple de requête** :
```http
POST /public/call.php
Content-Type: application/x-www-form-urlencoded

from_currency=CAD&to_currency=USD&method=getConversion
```

**Réponse** :
```json
{
    "from_currency": "CAD",
    "to_currency": "USD",
    "rate": 0.7234567890
}
```

#### 2. `getRates` - Récupération de tous les taux

**Exemple de requête** :
```http
POST /public/call.php
Content-Type: application/x-www-form-urlencoded

from_currency=EUR&to_currency=USD&method=getRates
```

**Réponse** :
```json
{
    "timestamp": 1640995200,
    "rates": {
        "USD": 1,
        "EUR": 0.8834,
        "GBP": 0.7456,
        "CAD": 1.2345,
        "...": "..."
    }
}
```

### Codes d'erreur

- `Devises d'entrée et de sortie manquantes` : Paramètres `from_currency` ou `to_currency` manquants
- `Méthode obligatoire` : Paramètre `method` manquant
- `Méthode inconnue` : Méthode non supportée
- `EUR non trouvé dans les taux` : Problème avec les données XE.com
- `Devise non trouvée dans les taux` : Code de devise invalide
- `Failed to retrieve data` : Erreur de connexion à XE.com
- `Failed to parse response` : Erreur de parsing JSON

## 🧪 Tests avec Postman

### Import de la collection

1. Ouvrez Postman
2. Cliquez sur "Import"
3. Sélectionnez le fichier `Currency.postman_collection.json`
4. La collection "Currency" sera ajoutée à votre workspace

### Collection incluse

La collection Postman contient :

- **getCurrency** : Exemple de conversion CAD → USD
  - Méthode : POST
  - URL : `http://currency.test/public/call.php`
  - Body : form-data avec `from_currency`, `to_currency`, et `method`

### Personnalisation

Modifiez les variables dans Postman :
- Changez l'URL selon votre configuration locale
- Testez différentes combinaisons de devises
- Essayez les deux méthodes disponibles

## 🔍 Logique de conversion

### Principe de base

1. **Source des données** : XE.com fournit les taux avec USD = 1 comme base
2. **Conversion vers EUR** : Le système recalcule tous les taux avec EUR comme référence
3. **Formule de conversion** :
   ```
   Taux_EUR_Base = Taux_EUR_USD / Taux_Devise_USD
   Conversion = (1 / Taux_From_EUR) × Taux_To_EUR
   ```

### Exemple concret

Si XE.com retourne :
- USD = 1
- EUR = 0.85
- CAD = 1.25

Le système calcule :
- EUR = 1 (référence)
- USD = 0.85/0.85 = 1.176
- CAD = 0.85/1.25 = 0.68

Pour convertir CAD → USD : (1/0.68) × 1.176 = 1.729

## ⚡ Optimisations

### Cache

- **Durée** : 2 heures
- **Stockage** : Variable statique en mémoire
- **Avantage** : Réduit les appels API et améliore les performances

### Headers HTTP

- **User-Agent** : Simule un navigateur moderne
- **Referer** : Référence dynamique selon les devises
- **Authorization** : Token d'authentification pour XE.com

## 🚨 Limitations

- **Source unique** : Dépend de XE.com (point de défaillance unique)
- **Taux de requête** : Limité par les restrictions de XE.com
- **Cache simple** : Pas de persistance entre les sessions
- **Pas d'historique** : Seuls les taux actuels sont disponibles

## 🔧 Maintenance

### Surveillance

- Vérifiez régulièrement la disponibilité de XE.com
- Surveillez les erreurs dans les logs du serveur web
- Testez périodiquement avec différentes devises

### Mise à jour

- **Headers HTTP** : Peuvent nécessiter une mise à jour si XE.com change
- **Token d'authentification** : À renouveler si expiré
- **URL API** : Vérifier si XE.com modifie son endpoint

## 📝 Développement

### Ajout de nouvelles fonctionnalités

1. **Nouvelles méthodes** : Ajoutez des cases dans le switch de `call.php`
2. **Nouvelles sources** : Créez de nouvelles classes dans `/classes/`
3. **Cache avancé** : Implémentez Redis ou fichiers pour la persistance

### Debug

- Activez `error_reporting` en développement
- Utilisez `var_dump()` pour inspecter les réponses XE.com
- Vérifiez les logs du serveur web

## 📊 Exemple d'utilisation complète

```php
<?php
// Exemple d'utilisation directe de la classe
require_once 'classes/xe.php';

$parameters = [
    'from_currency' => 'GBP',
    'to_currency' => 'JPY'
];

$xe = new XE($parameters);
$result = $xe->getConversion($parameters);

if (isset($result['error'])) {
    echo "Erreur : " . $result['error'];
} else {
    echo "1 {$result['from_currency']} = {$result['rate']} {$result['to_currency']}";
}
?>
```

## 🤝 Contribution

Les contributions sont les bienvenues ! Voici comment contribuer :

1. **Fork** le projet
2. **Créez** une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. **Committez** vos changements (`git commit -m 'Add some AmazingFeature'`)
4. **Push** vers la branche (`git push origin feature/AmazingFeature`)
5. **Ouvrez** une Pull Request

### Issues

Si vous trouvez un bug ou avez une suggestion :
- Ouvrez une [issue](../../issues)
- Décrivez le problème ou la fonctionnalité souhaitée
- Ajoutez des exemples si possible

## 📜 Licence

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

---

**Note** : Ce projet utilise l'API non-officielle de XE.com. Respectez leurs conditions d'utilisation et considérez l'utilisation de leur API officielle pour un usage en production.
