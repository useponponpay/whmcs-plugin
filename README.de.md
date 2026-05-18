# PolyPay WHMCS Zahlungs-Gateway-Plugin

Akzeptieren Sie Kryptowährungszahlungen (USDT, USDC usw.) in Ihrem WHMCS-Abrechnungssystem über [PolyPay](https://polypay.ai).

Unterstützte Netzwerke: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Voraussetzungen

> **⚠️ Bevor Sie dieses Plugin installieren, müssen Sie die folgenden Schritte auf [polypay.ai](https://polypay.ai) durchführen:**

1. **Konto registrieren** — Besuchen Sie [https://polypay.ai](https://polypay.ai) und melden Sie sich an
2. **Wallet-Adresse hinzufügen** — Gehen Sie zur **Wallet-Verwaltung** und fügen Sie mindestens eine Empfangs-Wallet hinzu (z.B. TRC20 USDT Adresse)
3. **Unterstützte Währungen aktivieren** — Wählen Sie aus, welche Kryptowährungen (USDT, USDC usw.) jede Wallet akzeptiert
4. **API Key erhalten** — Gehen Sie zur **API-Schlüssel**-Seite und generieren Sie einen API Key für die WHMCS-Integration

Ohne diese Schritte zeigt das Plugin den Fehler **„Keine verfügbaren Zahlungsmethoden"** an.

---

## Installation

Kopieren Sie die folgenden Dateien in Ihr WHMCS-Stammverzeichnis:

```
polypay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/polypay_config.php  →  includes/hooks/polypay_config.php
├── modules/gateways/polypay.php       →  modules/gateways/polypay.php
├── modules/gateways/callback/polypay.php → modules/gateways/callback/polypay.php
└── modules/gateways/polypay/          →  modules/gateways/polypay/
```

---

## Konfiguration

1. Melden Sie sich im **WHMCS Admin-Panel** an
2. Navigieren Sie zu **Einstellungen → Zahlungen → Zahlungs-Gateways**
3. Finden Sie **PolyPay** und klicken Sie auf **Aktivieren**
4. Geben Sie den **API Key** ein, den Sie von [polypay.ai](https://polypay.ai) erhalten haben
5. Klicken Sie auf **Änderungen speichern**

---

## Zahlungsablauf

```
Kunde bestellt → WHMCS erstellt Rechnung
    → Automatische Weiterleitung zur Rechnungsseite
    → Kunde wählt Netzwerk & Währung (z.B. Tron - USDT)
    → Plugin erstellt Zahlungsauftrag über PolyPay API
    → Kunde schließt Krypto-Zahlung ab
    → PolyPay sendet Callback → WHMCS markiert Rechnung als bezahlt
```

---

## Fehlerbehebung

| Problem | Lösung |
|---------|--------|
| „Keine verfügbaren Zahlungsmethoden" | Stellen Sie sicher, dass Sie Wallets hinzugefügt und Währungen auf [polypay.ai](https://polypay.ai) aktiviert haben |
| Zahlungs-Callback nicht empfangen | Prüfen Sie, ob Ihr WHMCS-Server öffentlich erreichbar ist und die Callback-URL korrekt ist |
| API Key Validierung fehlgeschlagen | Überprüfen Sie, ob der API Key korrekt ist und nicht abgelaufen ist |

---

## Links

- **PolyPay Konsole**: [https://polypay.ai](https://polypay.ai)
- **Dokumentation**: [https://polypay.ai/docs](https://polypay.ai/docs)
