# Plugin de Passerelle de Paiement PolyPay pour WHMCS

Acceptez les paiements en cryptomonnaies (USDT, USDC, etc.) dans votre système de facturation WHMCS via [PolyPay](https://polypay.ai).

Réseaux pris en charge : **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Prérequis

> **⚠️ Avant d'installer ce plugin, vous devez compléter les étapes suivantes sur [polypay.ai](https://polypay.ai) :**

1. **Créer un compte** — Visitez [https://polypay.ai](https://polypay.ai) et inscrivez-vous
2. **Ajouter une adresse de portefeuille** — Allez dans **Gestion des Portefeuilles** et ajoutez au moins un portefeuille de réception (ex. adresse USDT TRC20)
3. **Activer les devises** — Sélectionnez les cryptomonnaies (USDT, USDC, etc.) acceptées par chaque portefeuille
4. **Obtenir une API Key** — Allez sur la page **Clés API** et générez une API Key pour l'intégration WHMCS

Sans compléter ces étapes, le plugin affichera l'erreur **« Aucune méthode de paiement disponible »**.

---

## Installation

Copiez les fichiers suivants dans le répertoire racine de votre WHMCS :

```
polypay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/polypay_config.php  →  includes/hooks/polypay_config.php
├── modules/gateways/polypay.php       →  modules/gateways/polypay.php
├── modules/gateways/callback/polypay.php → modules/gateways/callback/polypay.php
└── modules/gateways/polypay/          →  modules/gateways/polypay/
```

---

## Configuration

1. Connectez-vous au **Panneau d'Administration WHMCS**
2. Naviguez vers **Configuration → Paiements → Passerelles de Paiement**
3. Trouvez **PolyPay** et cliquez sur **Activer**
4. Entrez la **API Key** obtenue depuis [polypay.ai](https://polypay.ai)
5. Cliquez sur **Enregistrer les Modifications**

---

## Flux de paiement

```
Le client passe commande → WHMCS crée la facture
    → Redirection automatique vers la page de facture
    → Le client sélectionne le réseau et la devise (ex. Tron - USDT)
    → Le plugin crée une commande de paiement via l'API PolyPay
    → Le client complète le paiement en crypto
    → PolyPay envoie le callback → WHMCS marque la facture comme payée
```

---

## Dépannage

| Problème | Solution |
|----------|----------|
| « Aucune méthode de paiement disponible » | Vérifiez que vous avez ajouté des portefeuilles et activé des devises sur [polypay.ai](https://polypay.ai) |
| Callback de paiement non reçu | Vérifiez que votre serveur WHMCS est accessible publiquement et que l'URL de callback est correcte |
| Échec de validation de l'API Key | Vérifiez que l'API Key est correcte et n'a pas expiré |

---

## Liens

- **Console PolyPay** : [https://polypay.ai](https://polypay.ai)
- **Documentation** : [https://polypay.ai/docs](https://polypay.ai/docs)
