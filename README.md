# PonponPay WHMCS 插件（ponponpay-plugin）

该目录为 PonponPay WHMCS 支付网关插件的**独立发布版本**，用于分发给客户的 WHMCS 环境。与 `whmcs/` 目录中的插件代码保持同步，但**生产环境 API 域名配置保持不变**。

## 目录结构

```text
ponponpay-plugin/
├─ includes/
│  └─ hooks/ponponpay_config.php   # 配置变更 Hook
└─ modules/
   └─ geteways/ponponpay/           # 网关主代码（发布时放到 WHMCS modules/gateways/）
      ├─ ponponpay.php              # 入口文件（对应 WHMCS modules/gateways/ponponpay.php）
      ├─ ponponpay_main.php         # 主要逻辑
      ├─ callback.php               # 回调入口
      ├─ hooks.php                  # 钩子
      ├─ ponponpay.js / ponponpay.css
      ├─ lib/                       # API 客户端与语言工具
      └─ lang/                      # 多语言包
```

> 注意：仓库内目录名为 `geteways`，发布到 WHMCS 时需放置到 `modules/gateways/`。

## 安装方式（目标 WHMCS 目录）

将以下文件复制到 WHMCS：

- `modules/gateways/ponponpay.php`
- `modules/gateways/ponponpay/*`
- `includes/hooks/ponponpay_config.php`

然后在 WHMCS 后台启用 PonponPay 网关并填写配置。

## 与 whmcs/ 目录同步规则

当 `whmcs/` 中的 PonponPay 插件有修改时，需要同步到本目录，**但不要同步 API 域名配置**：

- `whmcs/modules/gateways/ponponpay.php`
  → `ponponpay-plugin/modules/geteways/ponponpay/ponponpay.php`
- `whmcs/modules/gateways/ponponpay/*`
  → `ponponpay-plugin/modules/geteways/ponponpay/ponponpay/*`
- `whmcs/includes/hooks/ponponpay_config.php`
  → `ponponpay-plugin/includes/hooks/ponponpay_config.php`

## 注意事项

- 本目录用于发布版分发，保持生产环境 API 域名
- 本地开发环境特有配置不应同步到这里
- 语言包位于 `modules/geteways/ponponpay/lang/`
