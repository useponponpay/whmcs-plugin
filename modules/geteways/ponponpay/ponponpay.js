/**
 * PonponPay JavaScript Functions
 * 处理前端交互和支付流程
 */

// 全局配置
var PonponPay = {
    config: {
        checkInterval: 5000, // 5秒检查一次支付状态
        maxCheckTimes: 120,  // 最多检查10分钟
        currentCheckTimes: 0
    },

    // 初始化
    init: function() {
        this.bindEvents();
        this.initPaymentCheck();
    },

    // 绑定事件
    bindEvents: function() {
        // 复制地址按钮
        jQuery(document).on('click', '.coinpay-copy-address', function() {
            var address = jQuery(this).data('address');
            PonponPay.copyToClipboard(address);
            PonponPay.showMessage('地址已复制到剪贴板', 'success');
        });

        // 复制金额按钮
        jQuery(document).on('click', '.coinpay-copy-amount', function() {
            var amount = jQuery(this).data('amount');
            PonponPay.copyToClipboard(amount);
            PonponPay.showMessage('金额已复制到剪贴板', 'success');
        });

        // 查看二维码
        jQuery(document).on('click', '.coinpay-show-qr', function() {
            var qrUrl = jQuery(this).data('qr');
            PonponPay.showQRCode(qrUrl);
        });

        // 手动检查支付状态
        jQuery(document).on('click', '.coinpay-check-payment', function() {
            PonponPay.checkPaymentStatus();
        });
    },

    // 初始化支付状态检查
    initPaymentCheck: function() {
        if (jQuery('.coinpay-payment-info').length > 0) {
            this.startPaymentCheck();
        }
    },

    // 开始支付状态检查
    startPaymentCheck: function() {
        var self = this;
        var invoiceId = jQuery('.coinpay-payment-info').data('invoice-id');

        if (!invoiceId) return;

        var checkTimer = setInterval(function() {
            self.config.currentCheckTimes++;

            jQuery.post('modules/gateways/ponponpay/callback.php', {
                action: 'check_status',
                invoice_id: invoiceId
            }, function(response) {
                if (response.success) {
                    if (response.data.status === 'paid') {
                        clearInterval(checkTimer);
                        self.showMessage('支付成功！页面即将刷新...', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else if (response.data.status === 'expired') {
                        clearInterval(checkTimer);
                        self.showMessage('支付已过期，请重新生成订单', 'error');
                    }
                }

                // 检查次数超限
                if (self.config.currentCheckTimes >= self.config.maxCheckTimes) {
                    clearInterval(checkTimer);
                    self.showMessage('支付检查超时，请手动刷新页面检查支付状态', 'warning');
                }
            }, 'json').fail(function() {
                // 请求失败，停止检查
                clearInterval(checkTimer);
            });

        }, this.config.checkInterval);
    },

    // 手动检查支付状态
    checkPaymentStatus: function() {
        var invoiceId = jQuery('.coinpay-payment-info').data('invoice-id');
        if (!invoiceId) return;

        jQuery('.coinpay-check-payment').prop('disabled', true).text('检查中...');

        jQuery.post('modules/gateways/ponponpay/callback.php', {
            action: 'check_status',
            invoice_id: invoiceId
        }, function(response) {
            if (response.success) {
                if (response.data.status === 'paid') {
                    PonponPay.showMessage('支付成功！页面即将刷新...', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    PonponPay.showMessage('支付状态：' + response.data.status_text, 'info');
                }
            } else {
                PonponPay.showMessage('检查失败：' + response.message, 'error');
            }
        }, 'json').always(function() {
            jQuery('.coinpay-check-payment').prop('disabled', false).text('检查支付状态');
        });
    },

    // 复制到剪贴板
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            // 兼容旧浏览器
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    },

    // 显示二维码
    showQRCode: function(qrUrl) {
        var modal = jQuery('<div class="modal fade" tabindex="-1">');
        modal.html([
            '<div class="modal-dialog">',
            '<div class="modal-content">',
            '<div class="modal-header">',
            '<h5 class="modal-title">扫码支付</h5>',
            '<button type="button" class="close" data-dismiss="modal">&times;</button>',
            '</div>',
            '<div class="modal-body text-center">',
            '<img src="' + qrUrl + '" alt="Payment QR Code" class="img-fluid">',
            '<p class="mt-3">请使用钱包APP扫描上方二维码完成支付</p>',
            '</div>',
            '</div>',
            '</div>'
        ].join(''));

        jQuery('body').append(modal);
        modal.modal('show');

        modal.on('hidden.bs.modal', function() {
            modal.remove();
        });
    },

    // 显示消息
    showMessage: function(message, type) {
        var alertClass = 'alert-info';
        switch(type) {
            case 'success': alertClass = 'alert-success'; break;
            case 'error': alertClass = 'alert-danger'; break;
            case 'warning': alertClass = 'alert-warning'; break;
        }

        var alert = jQuery('<div class="alert ' + alertClass + ' alert-dismissible fade show">');
        alert.html(message + '<button type="button" class="close" data-dismiss="alert">&times;</button>');

        if (jQuery('.coinpay-messages').length > 0) {
            jQuery('.coinpay-messages').html(alert);
        } else {
            jQuery('.coinpay-payment-info').prepend('<div class="coinpay-messages"></div>');
            jQuery('.coinpay-messages').html(alert);
        }

        // 自动隐藏成功消息
        if (type === 'success') {
            setTimeout(function() {
                alert.alert('close');
            }, 3000);
        }
    }
};

// 页面加载完成后初始化
jQuery(document).ready(function() {
    PonponPay.init();
});