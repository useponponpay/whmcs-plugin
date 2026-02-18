# PonponPay WHMCS 決済ゲートウェイプラグイン

[PonponPay](https://ponponpay.com) を通じて、WHMCS 課金システムで暗号通貨決済（USDT、USDC など）を受け付けます。

対応ネットワーク：**Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## 前提条件

> **⚠️ このプラグインをインストールする前に、[ponponpay.com](https://ponponpay.com) で以下の手順を完了してください：**

1. **アカウント登録** — [https://ponponpay.com](https://ponponpay.com) にアクセスして登録
2. **ウォレットアドレスを追加** — **ウォレット管理** で受取用ウォレットを少なくとも1つ追加（例：TRC20 USDT アドレス）
3. **対応通貨を有効化** — ウォレットで受け付ける暗号通貨（USDT、USDC など）を選択
4. **API Key を取得** — **API キー** ページで WHMCS 連携用の API Key を生成

これらの手順を完了しないと、プラグインは **「利用可能な支払い方法がありません」** エラーを表示します。

---

## インストール

以下のファイルを WHMCS ルートディレクトリにコピーしてください：

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## 設定

1. **WHMCS 管理パネル** にログイン
2. **設定 → 支払い → 決済ゲートウェイ** に移動
3. **PonponPay** を見つけて **有効化** をクリック
4. [ponponpay.com](https://ponponpay.com) で取得した **API Key** を入力
5. **変更を保存** をクリック

---

## 決済フロー

```
顧客が注文 → WHMCS が請求書を作成
    → 請求書ページに自動リダイレクト
    → 顧客がネットワークと通貨を選択（例：Tron - USDT）
    → プラグインが PonponPay API で支払い注文を作成
    → 顧客が暗号通貨で支払い完了
    → PonponPay がコールバック送信 → WHMCS が請求書を支払い済みに更新
```

---

## トラブルシューティング

| 問題 | 解決策 |
|------|--------|
| 「利用可能な支払い方法がありません」 | [ponponpay.com](https://ponponpay.com) でウォレットの追加と通貨の有効化を確認 |
| 支払いコールバックが届かない | WHMCS サーバーが公開アクセス可能で、コールバック URL が正しいか確認 |
| API Key の検証失敗 | API Key が正しく、有効期限が切れていないか確認 |

---

## リンク

- **PonponPay コンソール**：[https://ponponpay.com](https://ponponpay.com)
- **ドキュメント**：[https://ponponpay.com/docs](https://ponponpay.com/docs)
