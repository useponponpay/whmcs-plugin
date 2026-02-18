# Plugin de Gateway de Pagamento PonponPay para WHMCS

Aceite pagamentos em criptomoedas (USDT, USDC, etc.) no seu sistema de faturamento WHMCS através do [PonponPay](https://ponponpay.com).

Redes suportadas: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Pré-requisitos

> **⚠️ Antes de instalar este plugin, você deve completar os seguintes passos em [ponponpay.com](https://ponponpay.com):**

1. **Registrar uma conta** — Visite [https://ponponpay.com](https://ponponpay.com) e cadastre-se
2. **Adicionar endereço de carteira** — Vá para **Gerenciamento de Carteiras** e adicione pelo menos uma carteira de recebimento (ex. endereço USDT TRC20)
3. **Ativar moedas** — Selecione quais criptomoedas (USDT, USDC, etc.) cada carteira aceita
4. **Obter API Key** — Vá para a página de **Chaves API** e gere uma API Key para integração com WHMCS

Sem completar estes passos, o plugin mostrará o erro **"Nenhum método de pagamento disponível"**.

---

## Instalação

Copie os seguintes arquivos para o diretório raiz do seu WHMCS:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## Configuração

1. Faça login no **Painel de Administração do WHMCS**
2. Navegue até **Configurações → Pagamentos → Gateways de Pagamento**
3. Encontre **PonponPay** e clique em **Ativar**
4. Insira a **API Key** obtida em [ponponpay.com](https://ponponpay.com)
5. Clique em **Salvar Alterações**

---

## Fluxo de pagamento

```
Cliente faz pedido → WHMCS cria fatura
    → Redirecionamento automático para a página da fatura
    → Cliente seleciona rede e moeda (ex. Tron - USDT)
    → Plugin cria ordem de pagamento via API PonponPay
    → Cliente completa o pagamento em cripto
    → PonponPay envia callback → WHMCS marca a fatura como paga
```

---

## Solução de problemas

| Problema | Solução |
|----------|---------|
| "Nenhum método de pagamento disponível" | Verifique se você adicionou carteiras e ativou moedas em [ponponpay.com](https://ponponpay.com) |
| Callback de pagamento não recebido | Verifique se seu servidor WHMCS é acessível publicamente e se a URL de callback está correta |
| Falha na validação da API Key | Verifique se a API Key está correta e não expirou |

---

## Links

- **Console PonponPay**: [https://ponponpay.com](https://ponponpay.com)
- **Documentação**: [https://docs.ponponpay.com](https://docs.ponponpay.com)
