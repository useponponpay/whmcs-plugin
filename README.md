🌐 [English](README.md) | [中文](README.zh.md) | [日本語](README.ja.md) | [한국어](README.ko.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Français](README.fr.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [العربية](README.ar.md)

# PolyPay WHMCS Payment Gateway Plugin

Accept cryptocurrency payments (USDT, USDC, etc.) in your WHMCS billing system via [PolyPay](https://polypay.ai).

Supported networks: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Prerequisites

> **⚠️ Before installing this plugin, you must complete the following steps on [polypay.ai](https://polypay.ai):**

1. **Register an account** — Visit [https://polypay.ai](https://polypay.ai) and sign up
2. **Add your wallet address** — Go to **Wallet Management** and add at least one receiving wallet (e.g. TRC20 USDT address)
3. **Enable supported currencies** — Select which cryptocurrencies (USDT, USDC, etc.) each wallet accepts
4. **Get your API Key** — Go to **API Keys** page and generate an API Key for WHMCS integration

Without completing these steps, the plugin will show **"No available payment methods"** error.

---

## Installation

Copy the following files into your WHMCS root directory:

```
polypay-plugin/                     →  YOUR_WHMCS_ROOT/
├── includes/hooks/polypay_config.php  →  includes/hooks/polypay_config.php
├── modules/gateways/polypay.php       →  modules/gateways/polypay.php
├── modules/gateways/callback/polypay.php → modules/gateways/callback/polypay.php
└── modules/gateways/polypay/          →  modules/gateways/polypay/
```

---

## Configuration

1. Log in to your **WHMCS Admin Panel**
2. Navigate to **Setup → Payments → Payment Gateways**
3. Find **PolyPay** and click **Activate**
4. Enter the **API Key** obtained from [polypay.ai](https://polypay.ai)
5. Click **Save Changes**

### Testing

Before enabling the gateway for live invoices:

1. Create a dedicated production API key in the PolyPay dashboard.
2. Enter that key in the WHMCS PolyPay gateway configuration.
3. Create a low-risk WHMCS invoice and choose PolyPay as the payment method.
4. Confirm the order and webhook delivery in the PolyPay dashboard.

---

## Directory Structure

```
polypay-plugin/
├── includes/
│   └── hooks/
│       └── polypay_config.php         # API URL & hook configuration
├── modules/
│   └── gateways/
│       ├── polypay.php                # Gateway entry point
│       ├── callback/
│       │   └── polypay.php            # Payment callback handler
│       └── polypay/
│           ├── polypay_main.php       # Core payment logic
│           ├── hooks.php                # WHMCS hooks (auto-redirect, etc.)
│           ├── admin_check.php          # Admin payment status check
│           ├── callback.php             # Callback processing
│           ├── polypay.js             # Frontend JavaScript
│           ├── polypay.css            # Frontend styles
│           ├── whmcs.json               # Module metadata
│           ├── lib/
│           │   ├── PolyPayApi.php     # API client
│           │   └── Language.php         # i18n support
│           └── lang/                    # Language packs (10 languages)
│               ├── english.php
│               ├── chinese.php
│               ├── japanese.php
│               ├── korean.php
│               ├── french.php
│               ├── german.php
│               ├── spanish.php
│               ├── portuguese.php
│               └── russian.php
└── README.md
```

---

## Payment Flow

```
Customer places order → WHMCS creates invoice
    → Auto-redirect to invoice page
    → Customer selects network & currency (e.g. Tron - USDT)
    → Plugin creates payment order via PolyPay API
    → Customer completes crypto payment
    → PolyPay sends callback → WHMCS marks invoice as paid
```

---

## Supported Languages

English, 中文, 日本語, 한국어, Français, Deutsch, Español, Português, Русский

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "No available payment methods" | Make sure you have added wallets and enabled currencies at [polypay.ai](https://polypay.ai) |
| Payment callback not received | Check that your WHMCS server is publicly accessible and the callback URL is correct |
| API Key validation failed | Verify the API Key is correct and has not expired |

---

## Links

- **PolyPay Console**: [https://polypay.ai](https://polypay.ai)
- **Documentation**: [https://polypay.ai/docs](https://polypay.ai/docs)
