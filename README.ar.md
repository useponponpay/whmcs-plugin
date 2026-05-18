# إضافة بوابة الدفع PolyPay لـ WHMCS

اقبل مدفوعات العملات المشفرة (USDT، USDC، إلخ) في نظام فواتير WHMCS الخاص بك عبر [PolyPay](https://polypay.ai).

الشبكات المدعومة: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## المتطلبات المسبقة

> **⚠️ قبل تثبيت هذه الإضافة، يجب إكمال الخطوات التالية على [polypay.ai](https://polypay.ai):**

1. **تسجيل حساب** — قم بزيارة [https://polypay.ai](https://polypay.ai) وسجل حسابًا
2. **إضافة عنوان محفظة** — انتقل إلى **إدارة المحافظ** وأضف محفظة استقبال واحدة على الأقل (مثل عنوان USDT TRC20)
3. **تفعيل العملات** — حدد العملات المشفرة (USDT، USDC، إلخ) التي تقبلها كل محفظة
4. **الحصول على API Key** — انتقل إلى صفحة **مفاتيح API** وأنشئ API Key لربط WHMCS

بدون إكمال هذه الخطوات، ستظهر الإضافة خطأ **"لا توجد طرق دفع متاحة"**.

---

## التثبيت

انسخ الملفات التالية إلى المجلد الرئيسي لـ WHMCS:

```
polypay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/polypay_config.php  →  includes/hooks/polypay_config.php
├── modules/gateways/polypay.php       →  modules/gateways/polypay.php
├── modules/gateways/callback/polypay.php → modules/gateways/callback/polypay.php
└── modules/gateways/polypay/          →  modules/gateways/polypay/
```

---

## الإعداد

1. سجل الدخول إلى **لوحة إدارة WHMCS**
2. انتقل إلى **الإعدادات → المدفوعات → بوابات الدفع**
3. ابحث عن **PolyPay** وانقر على **تفعيل**
4. أدخل **API Key** الذي حصلت عليه من [polypay.ai](https://polypay.ai)
5. انقر على **حفظ التغييرات**

---

## مسار الدفع

```
العميل يقدم طلبًا → WHMCS ينشئ فاتورة
    → إعادة توجيه تلقائية إلى صفحة الفاتورة
    → العميل يختار الشبكة والعملة (مثل Tron - USDT)
    → الإضافة تنشئ أمر دفع عبر API PolyPay
    → العميل يكمل الدفع بالعملة المشفرة
    → PolyPay يرسل callback → WHMCS يحدد الفاتورة كمدفوعة
```

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| "لا توجد طرق دفع متاحة" | تأكد من إضافة محافظ وتفعيل عملات على [polypay.ai](https://polypay.ai) |
| عدم استلام callback الدفع | تحقق من أن خادم WHMCS متاح للوصول العام وأن عنوان callback صحيح |
| فشل التحقق من API Key | تحقق من صحة API Key وعدم انتهاء صلاحيته |

---

## الروابط

- **لوحة تحكم PolyPay**: [https://polypay.ai](https://polypay.ai)
- **التوثيق**: [https://polypay.ai/docs](https://polypay.ai/docs)
