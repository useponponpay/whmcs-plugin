# Plugin de Gateway de Pagamento PolyPay para WHMCS

Aceite pagamentos em criptomoedas (USDT, USDC, etc.) no seu sistema de faturamento WHMCS através do [PolyPay](https://polypay.ai).

Redes suportadas: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Pré-requisitos

> **⚠️ Antes de instalar este plugin, você deve completar os seguintes passos em [polypay.ai](https://polypay.ai):**

1. **Registrar uma conta** — Visite [https://polypay.ai](https://polypay.ai) e cadastre-se
2. **Adicionar endereço de carteira** — Vá para **Gerenciamento de Carteiras** e adicione pelo menos uma carteira de recebimento (ex. endereço USDT TRC20)
3. **Ativar moedas** — Selecione quais criptomoedas (USDT, USDC, etc.) cada carteira aceita
4. **Obter API Key** — Vá para a página de **Chaves API** e gere uma API Key para integração com WHMCS

Sem completar estes passos, o plugin mostrará o erro **"Nenhum método de pagamento disponível"**.

---

## Instalação

Copie os seguintes arquivos para o diretório raiz do seu WHMCS:

```
polypay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/polypay_config.php  →  includes/hooks/polypay_config.php
├── modules/gateways/polypay.php       →  modules/gateways/polypay.php
├── modules/gateways/callback/polypay.php → modules/gateways/callback/polypay.php
└── modules/gateways/polypay/          →  modules/gateways/polypay/
```

---

## Configuração

1. Faça login no **Painel de Administração do WHMCS**
2. Navegue até **Configurações → Pagamentos → Gateways de Pagamento**
3. Encontre **PolyPay** e clique em **Ativar**
4. Insira a **API Key** obtida em [polypay.ai](https://polypay.ai)
5. Clique em **Salvar Alterações**

---

## Fluxo de pagamento

```
Cliente faz pedido → WHMCS cria fatura
    → Redirecionamento automático para a página da fatura
    → Cliente seleciona rede e moeda (ex. Tron - USDT)
    → Plugin cria ordem de pagamento via API PolyPay
    → Cliente completa o pagamento em cripto
    → PolyPay envia callback → WHMCS marca a fatura como paga
```

---

## Solução de problemas

| Problema | Solução |
|----------|---------|
| "Nenhum método de pagamento disponível" | Verifique se você adicionou carteiras e ativou moedas em [polypay.ai](https://polypay.ai) |
| Callback de pagamento não recebido | Verifique se seu servidor WHMCS é acessível publicamente e se a URL de callback está correta |
| Falha na validação da API Key | Verifique se a API Key está correta e não expirou |

---

## Links

- **Console PolyPay**: [https://polypay.ai](https://polypay.ai)
- **Documentação**: [https://polypay.ai/docs](https://polypay.ai/docs)
