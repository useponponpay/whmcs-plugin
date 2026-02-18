# PonponPay WHMCS Zahlungs-Gateway-Plugin

Akzeptieren Sie Kryptowährungszahlungen (USDT, USDC usw.) in Ihrem WHMCS-Abrechnungssystem über [PonponPay](https://ponponpay.com).

Unterstützte Netzwerke: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Voraussetzungen

> **⚠️ Bevor Sie dieses Plugin installieren, müssen Sie die folgenden Schritte auf [ponponpay.com](https://ponponpay.com) durchführen:**

1. **Konto registrieren** — Besuchen Sie [https://ponponpay.com](https://ponponpay.com) und melden Sie sich an
2. **Wallet-Adresse hinzufügen** — Gehen Sie zur **Wallet-Verwaltung** und fügen Sie mindestens eine Empfangs-Wallet hinzu (z.B. TRC20 USDT Adresse)
3. **Unterstützte Währungen aktivieren** — Wählen Sie aus, welche Kryptowährungen (USDT, USDC usw.) jede Wallet akzeptiert
4. **API Key erhalten** — Gehen Sie zur **API-Schlüssel**-Seite und generieren Sie einen API Key für die WHMCS-Integration

Ohne diese Schritte zeigt das Plugin den Fehler **„Keine verfügbaren Zahlungsmethoden"** an.

---

## Installation

Kopieren Sie die folgenden Dateien in Ihr WHMCS-Stammverzeichnis:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## Konfiguration

1. Melden Sie sich im **WHMCS Admin-Panel** an
2. Navigieren Sie zu **Einstellungen → Zahlungen → Zahlungs-Gateways**
3. Finden Sie **PonponPay** und klicken Sie auf **Aktivieren**
4. Geben Sie den **API Key** ein, den Sie von [ponponpay.com](https://ponponpay.com) erhalten haben
5. Klicken Sie auf **Änderungen speichern**

---

## Zahlungsablauf

```
Kunde bestellt → WHMCS erstellt Rechnung
    → Automatische Weiterleitung zur Rechnungsseite
    → Kunde wählt Netzwerk & Währung (z.B. Tron - USDT)
    → Plugin erstellt Zahlungsauftrag über PonponPay API
    → Kunde schließt Krypto-Zahlung ab
    → PonponPay sendet Callback → WHMCS markiert Rechnung als bezahlt
```

---

## Fehlerbehebung

| Problem | Lösung |
|---------|--------|
| „Keine verfügbaren Zahlungsmethoden" | Stellen Sie sicher, dass Sie Wallets hinzugefügt und Währungen auf [ponponpay.com](https://ponponpay.com) aktiviert haben |
| Zahlungs-Callback nicht empfangen | Prüfen Sie, ob Ihr WHMCS-Server öffentlich erreichbar ist und die Callback-URL korrekt ist |
| API Key Validierung fehlgeschlagen | Überprüfen Sie, ob der API Key korrekt ist und nicht abgelaufen ist |

---

## Links

- **PonponPay Konsole**: [https://ponponpay.com](https://ponponpay.com)
- **Dokumentation**: [https://docs.ponponpay.com](https://docs.ponponpay.com)
