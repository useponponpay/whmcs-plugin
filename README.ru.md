# Плагин платёжного шлюза PonponPay для WHMCS

Принимайте криптовалютные платежи (USDT, USDC и др.) в вашей системе биллинга WHMCS через [PonponPay](https://ponponpay.com).

Поддерживаемые сети: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## Предварительные требования

> **⚠️ Перед установкой этого плагина необходимо выполнить следующие шаги на [ponponpay.com](https://ponponpay.com):**

1. **Зарегистрировать аккаунт** — Перейдите на [https://ponponpay.com](https://ponponpay.com) и зарегистрируйтесь
2. **Добавить адрес кошелька** — Перейдите в **Управление кошельками** и добавьте хотя бы один кошелёк для приёма платежей (напр. адрес USDT TRC20)
3. **Включить валюты** — Выберите, какие криптовалюты (USDT, USDC и др.) принимает каждый кошелёк
4. **Получить API Key** — Перейдите на страницу **API-ключей** и сгенерируйте API Key для интеграции с WHMCS

Без выполнения этих шагов плагин покажет ошибку **«Нет доступных способов оплаты»**.

---

## Установка

Скопируйте следующие файлы в корневой каталог вашего WHMCS:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## Настройка

1. Войдите в **Панель администратора WHMCS**
2. Перейдите в **Настройки → Платежи → Платёжные шлюзы**
3. Найдите **PonponPay** и нажмите **Активировать**
4. Введите **API Key**, полученный на [ponponpay.com](https://ponponpay.com)
5. Нажмите **Сохранить изменения**

---

## Процесс оплаты

```
Клиент оформляет заказ → WHMCS создаёт счёт
    → Автоматическое перенаправление на страницу счёта
    → Клиент выбирает сеть и валюту (напр. Tron - USDT)
    → Плагин создаёт платёжный ордер через API PonponPay
    → Клиент завершает криптовалютный платёж
    → PonponPay отправляет callback → WHMCS отмечает счёт как оплаченный
```

---

## Устранение неполадок

| Проблема | Решение |
|----------|---------|
| «Нет доступных способов оплаты» | Убедитесь, что вы добавили кошельки и включили валюты на [ponponpay.com](https://ponponpay.com) |
| Callback платежа не получен | Проверьте, что ваш сервер WHMCS доступен публично и URL callback'а корректен |
| Ошибка валидации API Key | Проверьте, что API Key верен и не истёк |

---

## Ссылки

- **Консоль PonponPay**: [https://ponponpay.com](https://ponponpay.com)
- **Документация**: [https://docs.ponponpay.com](https://docs.ponponpay.com)
