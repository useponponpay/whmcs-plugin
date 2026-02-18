🌐 [English](README.md) | [中文](README.zh.md) | [日本語](README.ja.md) | [한국어](README.ko.md) | [Deutsch](README.de.md) | [Español](README.es.md) | [Français](README.fr.md) | [Português](README.pt.md) | [Русский](README.ru.md) | [العربية](README.ar.md)

# PonponPay WHMCS Payment Gateway Plugin

Accept cryptocurrency payments (USDT, USDC, etc.) in your WHMCS billing system via [PonponPay](https://ponponpay.com).

Supported networks: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Prerequisites

> **⚠️ Before installing this plugin, you must complete the following steps on [ponponpay.com](https://ponponpay.com):**

1. **Register an account** — Visit [https://ponponpay.com](https://ponponpay.com) and sign up
2. **Add your wallet address** — Go to **Wallet Management** and add at least one receiving wallet (e.g. TRC20 USDT address)
3. **Enable supported currencies** — Select which cryptocurrencies (USDT, USDC, etc.) each wallet accepts
4. **Get your API Key** — Go to **API Keys** page and generate an API Key for WHMCS integration

Without completing these steps, the plugin will show **"No available payment methods"** error.

---

## Installation

Copy the following files into your WHMCS root directory:

```
ponponpay-plugin/                     →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## Configuration

1. Log in to your **WHMCS Admin Panel**
2. Navigate to **Setup → Payments → Payment Gateways**
3. Find **PonponPay** and click **Activate**
4. Enter the **API Key** obtained from [ponponpay.com](https://ponponpay.com)
5. Click **Save Changes**

---

## Directory Structure

```
ponponpay-plugin/
├── includes/
│   └── hooks/
│       └── ponponpay_config.php         # API URL & hook configuration
├── modules/
│   └── gateways/
│       ├── ponponpay.php                # Gateway entry point
│       ├── callback/
│       │   └── ponponpay.php            # Payment callback handler
│       └── ponponpay/
│           ├── ponponpay_main.php       # Core payment logic
│           ├── hooks.php                # WHMCS hooks (auto-redirect, etc.)
│           ├── admin_check.php          # Admin payment status check
│           ├── callback.php             # Callback processing
│           ├── ponponpay.js             # Frontend JavaScript
│           ├── ponponpay.css            # Frontend styles
│           ├── whmcs.json               # Module metadata
│           ├── lib/
│           │   ├── PonponPayApi.php     # API client
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
    → Plugin creates payment order via PonponPay API
    → Customer completes crypto payment
    → PonponPay sends callback → WHMCS marks invoice as paid
```

---

## Supported Languages

English, 中文, 日本語, 한국어, Français, Deutsch, Español, Português, Русский

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| "No available payment methods" | Make sure you have added wallets and enabled currencies at [ponponpay.com](https://ponponpay.com) |
| Payment callback not received | Check that your WHMCS server is publicly accessible and the callback URL is correct |
| API Key validation failed | Verify the API Key is correct and has not expired |

---

## Links

- **PonponPay Console**: [https://ponponpay.com](https://ponponpay.com)
- **Documentation**: [https://ponponpay.com/docs](https://ponponpay.com/docs)
