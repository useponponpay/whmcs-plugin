# PonponPay WHMCS 支付网关插件

通过 [PonponPay](https://ponponpay.com) 在 WHMCS 计费系统中接受加密货币支付（USDT、USDC 等）。

支持网络：**Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## 前提条件

> **⚠️ 安装此插件之前，必须在 [ponponpay.com](https://ponponpay.com) 完成以下步骤：**

1. **注册账号** — 访问 [https://ponponpay.com](https://ponponpay.com) 注册
2. **添加收款钱包** — 进入 **钱包管理**，添加至少一个收款钱包地址（如 TRC20 USDT 地址）
3. **启用币种** — 为钱包选择支持的加密货币（USDT、USDC 等）
4. **获取 API Key** — 进入 **API 密钥** 页面，生成用于 WHMCS 集成的 API Key

未完成以上步骤，插件将显示 **"暂无可用的支付方式"** 错误。

---

## 安装

将以下文件复制到 WHMCS 根目录：

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## 配置

1. 登录 **WHMCS 管理后台**
2. 进入 **设置 → 支付 → 支付网关**
3. 找到 **PonponPay** 并点击 **激活**
4. 输入从 [ponponpay.com](https://ponponpay.com) 获取的 **API Key**
5. 点击 **保存**

---

## 支付流程

```
客户下单 → WHMCS 生成账单
    → 自动跳转到账单页面
    → 客户选择网络和币种（如 Tron - USDT）
    → 插件通过 PonponPay API 创建支付订单
    → 客户完成加密货币支付
    → PonponPay 发送回调 → WHMCS 标记账单为已支付
```

---

## 故障排查

| 问题 | 解决方案 |
|------|----------|
| "暂无可用的支付方式" | 确认已在 [ponponpay.com](https://ponponpay.com) 添加钱包并启用币种 |
| 未收到支付回调 | 检查 WHMCS 服务器是否可公网访问，回调 URL 是否正确 |
| API Key 验证失败 | 确认 API Key 正确且未过期 |

---

## 链接

- **PonponPay 控制台**：[https://ponponpay.com](https://ponponpay.com)
- **文档**：[https://docs.ponponpay.com](https://docs.ponponpay.com)
