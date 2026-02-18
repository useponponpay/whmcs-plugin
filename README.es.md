# Plugin de Pasarela de Pago PonponPay para WHMCS

Acepte pagos con criptomonedas (USDT, USDC, etc.) en su sistema de facturación WHMCS a través de [PonponPay](https://ponponpay.com).

Redes compatibles: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Requisitos previos

> **⚠️ Antes de instalar este plugin, debe completar los siguientes pasos en [ponponpay.com](https://ponponpay.com):**

1. **Registrar una cuenta** — Visite [https://ponponpay.com](https://ponponpay.com) y regístrese
2. **Agregar dirección de wallet** — Vaya a **Gestión de Wallets** y agregue al menos una wallet receptora (ej. dirección USDT TRC20)
3. **Habilitar monedas** — Seleccione qué criptomonedas (USDT, USDC, etc.) acepta cada wallet
4. **Obtener API Key** — Vaya a la página de **Claves API** y genere un API Key para la integración con WHMCS

Sin completar estos pasos, el plugin mostrará el error **"No hay métodos de pago disponibles"**.

---

## Instalación

Copie los siguientes archivos en el directorio raíz de su WHMCS:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## Configuración

1. Inicie sesión en el **Panel de Administración de WHMCS**
2. Navegue a **Configuración → Pagos → Pasarelas de Pago**
3. Busque **PonponPay** y haga clic en **Activar**
4. Ingrese el **API Key** obtenido de [ponponpay.com](https://ponponpay.com)
5. Haga clic en **Guardar Cambios**

---

## Flujo de pago

```
Cliente realiza pedido → WHMCS crea factura
    → Redirección automática a la página de factura
    → Cliente selecciona red y moneda (ej. Tron - USDT)
    → Plugin crea orden de pago mediante PonponPay API
    → Cliente completa el pago con criptomonedas
    → PonponPay envía callback → WHMCS marca la factura como pagada
```

---

## Solución de problemas

| Problema | Solución |
|----------|----------|
| "No hay métodos de pago disponibles" | Verifique que ha agregado wallets y habilitado monedas en [ponponpay.com](https://ponponpay.com) |
| Callback de pago no recibido | Compruebe que su servidor WHMCS es accesible públicamente y la URL de callback es correcta |
| Validación de API Key fallida | Verifique que el API Key es correcto y no ha expirado |

---

## Enlaces

- **Consola PonponPay**: [https://ponponpay.com](https://ponponpay.com)
- **Documentación**: [https://ponponpay.com/docs](https://ponponpay.com/docs)
