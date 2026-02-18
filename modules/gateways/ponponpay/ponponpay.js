/**
 * PonponPay JavaScript Functions
 * Handle frontend interactions and payment flow
 */

// Global configuration
var PonponPay = {
    config: {
        checkInterval: 5000, // Check payment status every 5 seconds
        maxCheckTimes: 120,  // Maximum check for 10 minutes
        currentCheckTimes: 0
    },

    // Initialize
    init: function() {
        this.bindEvents();
        this.initPaymentCheck();
    },

    // Bind events
    bindEvents: function() {
        // Copy address button
        jQuery(document).on('click', '.coinpay-copy-address', function() {
            var address = jQuery(this).data('address');
            PonponPay.copyToClipboard(address);
            PonponPay.showMessage('Address copied to clipboard', 'success');
        });

        // Copy amount button
        jQuery(document).on('click', '.coinpay-copy-amount', function() {
            var amount = jQuery(this).data('amount');
            PonponPay.copyToClipboard(amount);
            PonponPay.showMessage('Amount copied to clipboard', 'success');
        });

        // View QR code
        jQuery(document).on('click', '.coinpay-show-qr', function() {
            var qrUrl = jQuery(this).data('qr');
            PonponPay.showQRCode(qrUrl);
        });

        // Manual check payment status
        jQuery(document).on('click', '.coinpay-check-payment', function() {
            PonponPay.checkPaymentStatus();
        });
    },

    // Initialize payment status check
    initPaymentCheck: function() {
        if (jQuery('.coinpay-payment-info').length > 0) {
            this.startPaymentCheck();
        }
    },

    // Start payment status check
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
                        self.showMessage('Payment successful! Page will refresh...', 'success');
                        setTimeout(function() {
                            location.reload();
                        }, 2000);
                    } else if (response.data.status === 'expired') {
                        clearInterval(checkTimer);
                        self.showMessage('Payment expired, please create a new order', 'error');
                    }
                }

                // Check times exceeded
                if (self.config.currentCheckTimes >= self.config.maxCheckTimes) {
                    clearInterval(checkTimer);
                    self.showMessage('Payment check timeout, please refresh the page manually to check payment status', 'warning');
                }
            }, 'json').fail(function() {
                // Request failed, stop checking
                clearInterval(checkTimer);
            });

        }, this.config.checkInterval);
    },

    // Manual check payment status
    checkPaymentStatus: function() {
        var invoiceId = jQuery('.coinpay-payment-info').data('invoice-id');
        if (!invoiceId) return;

        jQuery('.coinpay-check-payment').prop('disabled', true).text('Checking...');

        jQuery.post('modules/gateways/ponponpay/callback.php', {
            action: 'check_status',
            invoice_id: invoiceId
        }, function(response) {
            if (response.success) {
                if (response.data.status === 'paid') {
                    PonponPay.showMessage('Payment successful! Page will refresh...', 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    PonponPay.showMessage('Payment status: ' + response.data.status_text, 'info');
                }
            } else {
                PonponPay.showMessage('Check failed: ' + response.message, 'error');
            }
        }, 'json').always(function() {
            jQuery('.coinpay-check-payment').prop('disabled', false).text('Check Payment Status');
        });
    },

    // Copy to clipboard
    copyToClipboard: function(text) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text);
        } else {
            // Fallback for older browsers
            var textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        }
    },

    // Show QR code
    showQRCode: function(qrUrl) {
        var modal = jQuery('<div class="modal fade" tabindex="-1">');
        modal.html([
            '<div class="modal-dialog">',
            '<div class="modal-content">',
            '<div class="modal-header">',
            '<h5 class="modal-title">Scan to Pay</h5>',
            '<button type="button" class="close" data-dismiss="modal">&times;</button>',
            '</div>',
            '<div class="modal-body text-center">',
            '<img src="' + qrUrl + '" alt="Payment QR Code" class="img-fluid">',
            '<p class="mt-3">Please scan the QR code above with your wallet app to complete payment</p>',
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

    // Show message
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

        // Auto hide success message
        if (type === 'success') {
            setTimeout(function() {
                alert.alert('close');
            }, 3000);
        }
    }
};

// Initialize when page is loaded
jQuery(document).ready(function() {
    PonponPay.init();
});
