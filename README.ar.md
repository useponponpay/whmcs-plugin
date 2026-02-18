# إضافة بوابة الدفع PonponPay لـ WHMCS

اقبل مدفوعات العملات المشفرة (USDT، USDC، إلخ) في نظام فواتير WHMCS الخاص بك عبر [PonponPay](https://ponponpay.com).

الشبكات المدعومة: **Tron (TRC20)** · **Ethereum (ERC20)** · **BSC (BEP20)** · **Polygon** · **Solana**

---

## المتطلبات المسبقة

> **⚠️ قبل تثبيت هذه الإضافة، يجب إكمال الخطوات التالية على [ponponpay.com](https://ponponpay.com):**

1. **تسجيل حساب** — قم بزيارة [https://ponponpay.com](https://ponponpay.com) وسجل حسابًا
2. **إضافة عنوان محفظة** — انتقل إلى **إدارة المحافظ** وأضف محفظة استقبال واحدة على الأقل (مثل عنوان USDT TRC20)
3. **تفعيل العملات** — حدد العملات المشفرة (USDT، USDC، إلخ) التي تقبلها كل محفظة
4. **الحصول على API Key** — انتقل إلى صفحة **مفاتيح API** وأنشئ API Key لربط WHMCS

بدون إكمال هذه الخطوات، ستظهر الإضافة خطأ **"لا توجد طرق دفع متاحة"**.

---

## التثبيت

انسخ الملفات التالية إلى المجلد الرئيسي لـ WHMCS:

```
ponponpay-plugin/                        →  YOUR_WHMCS_ROOT/
├── includes/hooks/ponponpay_config.php  →  includes/hooks/ponponpay_config.php
├── modules/gateways/ponponpay.php       →  modules/gateways/ponponpay.php
├── modules/gateways/callback/ponponpay.php → modules/gateways/callback/ponponpay.php
└── modules/gateways/ponponpay/          →  modules/gateways/ponponpay/
```

---

## الإعداد

1. سجل الدخول إلى **لوحة إدارة WHMCS**
2. انتقل إلى **الإعدادات → المدفوعات → بوابات الدفع**
3. ابحث عن **PonponPay** وانقر على **تفعيل**
4. أدخل **API Key** الذي حصلت عليه من [ponponpay.com](https://ponponpay.com)
5. انقر على **حفظ التغييرات**

---

## مسار الدفع

```
العميل يقدم طلبًا → WHMCS ينشئ فاتورة
    → إعادة توجيه تلقائية إلى صفحة الفاتورة
    → العميل يختار الشبكة والعملة (مثل Tron - USDT)
    → الإضافة تنشئ أمر دفع عبر API PonponPay
    → العميل يكمل الدفع بالعملة المشفرة
    → PonponPay يرسل callback → WHMCS يحدد الفاتورة كمدفوعة
```

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| "لا توجد طرق دفع متاحة" | تأكد من إضافة محافظ وتفعيل عملات على [ponponpay.com](https://ponponpay.com) |
| عدم استلام callback الدفع | تحقق من أن خادم WHMCS متاح للوصول العام وأن عنوان callback صحيح |
| فشل التحقق من API Key | تحقق من صحة API Key وعدم انتهاء صلاحيته |

---

## الروابط

- **لوحة تحكم PonponPay**: [https://ponponpay.com](https://ponponpay.com)
- **التوثيق**: [https://docs.ponponpay.com](https://docs.ponponpay.com)
