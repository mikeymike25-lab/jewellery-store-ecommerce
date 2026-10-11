@extends('checkout.layout')

@section('title', 'Order Confirmation')

@php
    $currentStep = 4;
@endphp

@section('content')
<div class="bg-white p-10 border border-gray-100">
    @if(($payment_status ?? 'pending') === 'failed')
        <!-- Payment Failed -->
        <div class="text-center mb-16 mt-8 animate-fade-in">
            <div class="mx-auto flex items-center justify-center h-20 w-20 bg-[#FAFAFA] border border-red-50 mb-8 rounded-full">
                <i data-lucide="x" class="h-6 w-6 text-red-300"></i>
            </div>
            <h1 class="text-3xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">Transaction Unfulfilled</h1>
            <p class="text-gray-400 mb-10 font-light leading-relaxed max-w-md mx-auto">{{ $errorMessage ?? 'Our treasury was unable to finalize your acquisition at this moment. We invite you to attempt a different method.' }}</p>
            
            <div class="max-w-md mx-auto bg-[#FAFAFA] border border-gray-50 p-8 text-left">
                <p class="text-[9px] mono tracking-[0.25em] text-gray-400 uppercase mb-6 font-medium text-center">ANALYTICS & RESOLUTION</p>
                <ul class="text-[11px] text-gray-400 space-y-4 mono uppercase tracking-wider">
                    <li class="flex items-center"><span class="w-1.5 h-1.5 bg-red-200 rounded-full mr-4"></span> INSUFFICIENT TREASURY</li>
                    <li class="flex items-center"><span class="w-1.5 h-1.5 bg-red-200 rounded-full mr-4"></span> EXPIRED CREDENTIALS</li>
                    <li class="flex items-center"><span class="w-1.5 h-1.5 bg-red-200 rounded-full mr-4"></span> GATEWAY TIMEOUT</li>
                </ul>
            </div>
        </div>

        <!-- Error Details -->
        <div class="border-t border-gray-50 pt-12 mb-12 text-center">
            <div class="mb-12">
                <p class="text-[9px] mono tracking-[0.25em] text-gray-300 uppercase mb-3">ORDER MANDATE</p>
                <p class="text-2xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">#{{ $order->order_number }}</p>
                <p class="text-[11px] text-gray-400 mono uppercase tracking-[0.2em]">Acquisition Value: <span class="text-[#1A1A1A] font-medium">€{{ number_format($order->total_amount, 2) }}</span></p>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-center items-center gap-6">
                <a href="{{ route('payments.xendit.pay', ['order' => $order->id]) }}" 
                   class="btn-gold w-full sm:w-auto">
                    REATTEMPT ACQUISITION
                </a>
                <a href="{{ route('checkout.payment') }}" 
                   class="px-10 py-4 text-[10px] mono tracking-[0.3em] uppercase transition-all duration-300 border border-gray-100 text-gray-400 hover:text-[#1A1A1A] hover:border-[#1A1A1A] text-center w-full sm:w-auto">
                    AMEND METHOD
                </a>
            </div>
        </div>
    @elseif(($payment_status ?? 'pending') === 'paid')
        <!-- Payment Successful -->
        <div class="text-center mb-16 mt-8 animate-fade-in">
            <div class="mx-auto flex items-center justify-center h-20 w-20 bg-[#FAFAFA] border border-gray-50 mb-8 rounded-full">
                <i data-lucide="check" class="h-6 w-6 text-[#B6965D]"></i>
            </div>
            <h1 class="text-4xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">Payment Verified</h1>
            <p class="text-sm text-gray-400 font-light tracking-wide uppercase mono">{{ $successMessage ?? 'Your acquisition has been elegantly authenticated.' }}</p>
        </div>

        <!-- Order Summary -->
        <div class="border-t border-b border-gray-50 py-16 mb-12 flex flex-col items-center">
            <div class="text-center mb-12">
                <p class="text-[9px] mono tracking-[0.25em] text-gray-300 uppercase mb-3">ORDER MANDATE</p>
                <p class="text-2xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">#{{ $order->order_number }}</p>
                <p class="text-[11px] text-gray-400 mono uppercase tracking-[0.2em]">Final Value: <span class="text-[#1A1A1A] font-medium">€{{ number_format($order->total_amount, 2) }}</span></p>
            </div>

            <!-- Payment Info -->
            <div class="text-center max-w-md mx-auto">
                <p class="text-sm text-gray-400 mb-12 leading-relaxed font-light">
                    Your exquisite selection is now being prepared in our atelier. You will receive an email confirmation with tracking details once dispatched.
                </p>
                <a href="{{ route('checkout.summary', ['order' => $order->order_number]) }}" 
                   class="btn-gold inline-block">
                    VIEW DIGITAL RECEIPT
                </a>
            </div>
        </div>
    @else
        <!-- Loading / Redirecting State -->
        <div class="text-center mb-16 mt-8 animate-fade-in">
            <div class="mx-auto flex items-center justify-center h-20 w-20 mb-8 overflow-hidden rounded-full bg-[#FAFAFA]">
                <div class="animate-spin h-6 w-6 border-b border-[#1A1A1A]"></div>
            </div>
            <h1 class="text-3xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">Securing Protocol</h1>
            <p class="text-sm text-gray-400 font-light tracking-wide uppercase mono">Establishing a fortified connection to our treasury portal...</p>
        </div>

        <!-- Order Summary -->
        <div class="border-t border-b border-gray-50 py-16 mb-12 flex flex-col items-center">
            <div class="text-center mb-12">
                <p class="text-[9px] mono tracking-[0.25em] text-gray-300 uppercase mb-3">ORDER MANDATE</p>
                <p class="text-2xl text-[#1A1A1A] mb-4 font-playfair tracking-tight">#{{ $order->order_number }}</p>
                <p class="text-[11px] text-gray-400 mono uppercase tracking-[0.2em]">Mandate Value: <span class="text-[#1A1A1A] font-medium">€{{ number_format($order->total_amount, 2) }}</span></p>
            </div>

            <!-- Payment Info -->
            <div class="text-center max-w-md mx-auto">
                <p class="text-sm text-gray-400 mb-10 leading-relaxed font-light">
                    You are being transferred to Xendit's encrypted environment to finalize your transaction with the utmost security.
                </p>
                <div id="popup-blocked-message" style="display: none;" class="mb-10 p-8 border border-gray-50 text-left bg-[#FAFAFA]">
                    <p class="text-[9px] mono tracking-[0.2em] text-[#1A1A1A] mb-4 font-medium uppercase">
                        <i data-lucide="alert-triangle" class="w-3 h-3 inline mr-2 text-[#B6965D]"></i>
                        Gateway Restricted
                    </p>
                    <p class="text-xs text-gray-400 leading-relaxed font-light">
                        Pop-up access has been restricted by your browser. Please permit access or use the portal entry below to proceed.
                    </p>
                </div>
                <a 
                   href="{{ route('payments.xendit.pay', ['order' => $order->id]) }}"
                   id="manual-payment-link"
                   onclick="if (!/Android|iPhone|iPad|Mobile|wv/i.test(navigator.userAgent)) { event.preventDefault(); openPaymentGatewayManual(this.href, 'XenditPayment_{{ $order->order_number }}'); }"
                   class="btn-gold inline-block text-center cursor-pointer">
                    ENTER SECURE PORTAL
                </a>
            </div>
        </div>
    @endif

    <!-- Support -->
    <div class="text-center mt-16 mb-4">
        <p class="text-[9px] mono tracking-[0.25em] text-gray-300 uppercase">
            REQUIRING ASSISTANCE? <a href="#" class="text-[#1A1A1A] hover:text-[#B6965D] transition-colors pb-1 border-b border-gray-100 hover:border-[#1A1A1A] ml-2">CONCIERGE REACH</a>
        </p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
    
    // Payment gateway - user must manually click button to open
    // This ensures browsers respect the popup window request (user-initiated clicks are more likely to open as popup)
           @if($order->payment_method !== 'Cash on Delivery' && ($payment_status ?? 'pending') === 'pending' && !($isReturnFromPayment ?? false))
               (function() {
                       // Store reference for window close detection
                       window.paymentWindowRef = null;
                       window.paymentWindowCheckInterval = null;
            
                   // Listen for window focus/blur events - when user returns to this tab after payment, start checking
            let wasBlurred = false;
            window.addEventListener('blur', function() {
                wasBlurred = true;
                console.log('Window blurred (user likely switched tabs/windows)');
            });
            
            window.addEventListener('focus', function() {
                if (wasBlurred && window.paymentWindowRef && !window.paymentPollingActive) {
                    console.log('Window regained focus, checking if payment window is still open');
                    wasBlurred = false;
                    try {
                        if (window.paymentWindowRef.closed) {
                                   console.log('Payment window is closed (detected on focus), checking payment status immediately');
                                   // Immediately check payment status when window is closed
                                   fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                                       headers: {
                                           'X-Requested-With': 'XMLHttpRequest',
                                           'Accept': 'application/json',
                                       },
                                       method: 'GET',
                                       credentials: 'same-origin',
                                       cache: 'no-cache'
                                   })
                                   .then(response => {
                                       if (response.ok) {
                                           return response.json();
                                       }
                                       throw new Error('Network response was not ok');
                                   })
                                   .then(data => {
                                       if (data && data.payment_status === 'paid') {
                                           console.log('✅ Payment successful detected on focus! Refreshing page...');
                                           window.location.reload();
                                       } else {
                                           // If not paid yet, start polling
                                           startPaymentStatusPolling();
                                       }
                                   })
                                   .catch(error => {
                                       console.error('Payment status check failed on focus:', error);
                                       // Start polling as fallback
                            startPaymentStatusPolling();
                                   });
                        } else {
                                   // Window might still be open, but check payment status anyway
                                   // (user might have completed payment and window will auto-close)
                                   fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                                       headers: {
                                           'X-Requested-With': 'XMLHttpRequest',
                                           'Accept': 'application/json',
                                       },
                                       method: 'GET',
                                       credentials: 'same-origin',
                                       cache: 'no-cache'
                                   })
                                   .then(response => {
                                       if (response.ok) {
                                           return response.json();
                                       }
                                       throw new Error('Network response was not ok');
                                   })
                                   .then(data => {
                                       if (data && data.payment_status === 'paid') {
                                           console.log('✅ Payment successful detected on focus (window still open)! Refreshing page...');
                                           window.location.reload();
                                       } else if (!window.paymentPollingActive) {
                                           // Start polling as backup
                            setTimeout(function() {
                                if (!window.paymentPollingActive) {
                                    console.log('Starting polling after focus (payment window may have closed)');
                                    startPaymentStatusPolling();
                                }
                            }, 2000);
                        }
                                   })
                                   .catch(error => {
                                       console.error('Payment status check failed on focus:', error);
                                       if (!window.paymentPollingActive) {
                                           setTimeout(function() {
                                               if (!window.paymentPollingActive) {
                        startPaymentStatusPolling();
                    }
                                           }, 2000);
                                       }
                                   });
                                                    }
                                                } catch (e) {
                               // Cross-origin - assume closed and check payment status
                               console.log('Payment window likely closed (cross-origin on focus), checking payment status');
                               fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                                   headers: {
                                       'X-Requested-With': 'XMLHttpRequest',
                                       'Accept': 'application/json',
                                   },
                                   method: 'GET',
                                   credentials: 'same-origin',
                                   cache: 'no-cache'
                               })
                               .then(response => {
                                   if (response.ok) {
                                       return response.json();
                                    }
                                   throw new Error('Network response was not ok');
                               })
                               .then(data => {
                                   if (data && data.payment_status === 'paid') {
                                       console.log('✅ Payment successful detected on focus (cross-origin)! Refreshing page...');
                                       window.location.reload();
                        } else {
                                                    startPaymentStatusPolling();
                                                }
                               })
                               .catch(error => {
                                   console.error('Payment status check failed on focus (cross-origin):', error);
                                    startPaymentStatusPolling();
                               });
                                }
                       }
                   });
            
            // Function to start payment status polling (can be called immediately when window closes)
            // Make it globally accessible
            window.startPaymentStatusPolling = function() {
                // Don't start if already polling
                if (window.paymentPollingActive) {
                    console.log('Payment polling already active');
                    return;
                }
                window.paymentPollingActive = true;
                
                console.log('Starting payment status polling immediately');
                let pollCount = 0;
                const maxPolls = 120; // Poll for 4 minutes max (120 polls at 2 seconds each)
                
                // Do first check immediately, then continue with interval
                function checkPaymentStatus() {
                    pollCount++;
                    console.log('Polling payment status, attempt:', pollCount);
                    
                    if (pollCount >= maxPolls) {
                        if (window.paymentPollInterval) {
                            clearInterval(window.paymentPollInterval);
                            window.paymentPollInterval = null;
                        }
                        window.paymentPollingActive = false;
                        console.log('Max polls reached, stopping');
                        return;
                    }
                    
                    // Use poll parameter to get JSON status without triggering gateway opening
                    fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-cache'
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Network response was not ok');
                    })
                    .then(data => {
                        if (!data || !data.payment_status) {
                            if (pollCount <= 3) { // Only log first few attempts to avoid spam
                                console.log('No payment status in response:', data);
                            }
                            return;
                        }
                        
                        console.log('Current payment status:', data.payment_status, '- Poll #' + pollCount);
                        
                        // Handle JSON response
                        if (data.payment_status === 'paid') {
                            console.log('✅✅✅ PAYMENT SUCCESS DETECTED! Reloading page immediately...');
                            if (window.paymentPollInterval) {
                                clearInterval(window.paymentPollInterval);
                                window.paymentPollInterval = null;
                            }
                            window.paymentPollingActive = false;
                            // Clear any window check intervals
                            if (window.paymentWindowCheckInterval) {
                                clearInterval(window.paymentWindowCheckInterval);
                                window.paymentWindowCheckInterval = null;
                            }
                            // Reload immediately
                            window.location.reload();
                        } else if (data.payment_status === 'failed') {
                            console.log('❌ Payment status changed to failed, reloading page');
                            if (window.paymentPollInterval) {
                                clearInterval(window.paymentPollInterval);
                                window.paymentPollInterval = null;
                            }
                            window.paymentPollingActive = false;
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Payment status check failed:', error);
                    });
                }
                
                // Check immediately on first call (no delay)
                checkPaymentStatus();
                
                // Then continue checking every 1 second for faster detection
                window.paymentPollInterval = setInterval(checkPaymentStatus, 1000);
            };
               })();
           @endif
    
    // Continuous polling for payment status (always active when payment is pending)
    // This will detect payment success even if user doesn't return to this page
    // Start polling after 15 seconds (give webhook time to process after payment)
    // Note: If payment window closes, polling starts immediately via startPaymentStatusPolling()
    @if($order->payment_method !== 'Cash on Delivery' && ($payment_status ?? 'pending') === 'pending')
        (function() {
            // Only start background polling if not already started by window close detection
            if (window.paymentPollingActive) {
                console.log('Payment polling already active (started by window close detection)');
                return;
            }
            
            console.log('Setting up background payment status polling');
            let pollCount = 0;
            const maxPolls = 60; // Poll for 2 minutes max (60 polls at 2 seconds each)
            let pollInterval = null;
            
            // Start polling after 10 seconds as fallback (if window close detection didn't trigger)
            setTimeout(function() {
                // Don't start if already polling from window close
                if (window.paymentPollingActive) {
                    console.log('Payment polling already active (started by window close), skipping background polling');
                    return;
                }
                
                console.log('Starting background payment status polling (fallback)');
                window.paymentPollingActive = true;
                let pollCount = 0;
                const maxPolls = 90; // Poll for 3 minutes max
                
                function checkPaymentStatus() {
                    pollCount++;
                    console.log('Background polling payment status, attempt:', pollCount);
                    
                    if (pollCount >= maxPolls) {
                        if (pollInterval) {
                            clearInterval(pollInterval);
                        }
                        window.paymentPollingActive = false;
                        console.log('Max polls reached, stopping');
                        return;
                    }
                    
                    // Use poll parameter to get JSON status without triggering gateway opening
                    fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-cache'
                    })
                    .then(response => {
                        if (response.ok) {
                            return response.json();
                        }
                        throw new Error('Network response was not ok');
                    })
                    .then(data => {
                        if (!data || !data.payment_status) return;
                        
                        console.log('Background check - Current payment status:', data.payment_status);
                        
                        // Handle JSON response
                        if (data.payment_status === 'paid') {
                            console.log('✅ Background polling: Payment status changed to paid, reloading page');
                            if (pollInterval) {
                                clearInterval(pollInterval);
                            }
                            window.paymentPollingActive = false;
                            window.location.reload();
                        } else if (data.payment_status === 'failed') {
                            console.log('❌ Background polling: Payment status changed to failed, reloading page');
                            if (pollInterval) {
                                clearInterval(pollInterval);
                            }
                            window.paymentPollingActive = false;
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Payment status check failed:', error);
                    });
                }
                
                // Check immediately
                checkPaymentStatus();
                
                // Then continue every 1 second for faster detection
                pollInterval = setInterval(checkPaymentStatus, 1000);
            }, 5000); // Start polling 5 seconds after page load (faster fallback)
        })();
    @endif
    
    // Function for manual button click - opens as window (popup) when triggered by user click
    // User clicks are more likely to be respected by browsers for popup windows
    window.openPaymentGatewayManual = function(url, windowName) {
        console.log('Manual payment gateway open requested');
        const isMobileOrWebView = /Android|iPhone|iPad|iPod|Mobile|wv/i.test(navigator.userAgent);
        if (isMobileOrWebView) {
            window.location.href = url;
            return;
        }

        try {
            // Force window opening (not tab) with explicit window features
            const windowFeatures = [
                'width=1200',
                'height=800',
                'left=' + Math.round((screen.width - 1200) / 2),
                'top=' + Math.round((screen.height - 800) / 2),
                'resizable=yes',
                'scrollbars=yes',
                'status=yes',
                'toolbar=no',
                'menubar=no',
                'location=yes',
                'noopener=yes',
                'noreferrer=yes',
                'popup=yes'
            ].join(',');
            
            console.log('Opening payment gateway manually with features:', windowFeatures);
            const manualWindow = window.open(url, windowName, windowFeatures);
            
            if (manualWindow) {
                window.paymentWindowRef = manualWindow;
                
                // Hide the button
                const manualLink = document.getElementById('manual-payment-link');
                if (manualLink) {
                    manualLink.style.display = 'none';
                }
                
                // Update message
                const countdownParent = document.getElementById('redirect-countdown');
                if (countdownParent) {
                    countdownParent.innerHTML = '<span class="text-green-600 font-medium">Payment gateway opened. Please complete payment there. This page will automatically update when payment is successful.</span>';
                }
                
                const popupBlockedMsg = document.getElementById('popup-blocked-message');
                if (popupBlockedMsg) {
                    popupBlockedMsg.style.display = 'none';
                }
                
                // Start window close detection
                window.paymentWindowCheckInterval = setInterval(function() {
                    try {
                        if (!manualWindow || manualWindow.closed) {
                            console.log('Manual payment gateway window closed, checking payment status immediately');
                            if (window.paymentWindowCheckInterval) {
                                clearInterval(window.paymentWindowCheckInterval);
                                window.paymentWindowCheckInterval = null;
                            }
                            window.paymentWindowRef = null;
                            
                            // Immediately check payment status when window closes
                            fetch('{{ route('checkout.confirmation', ['order' => $order->order_number]) }}?poll=1', {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'Accept': 'application/json',
                                },
                                method: 'GET',
                                credentials: 'same-origin',
                                cache: 'no-cache'
                            })
                            .then(response => {
                                if (response.ok) {
                                    return response.json();
                                }
                                throw new Error('Network response was not ok');
                            })
                            .then(data => {
                                if (data && data.payment_status === 'paid') {
                                    console.log('✅ Payment successful detected on window close! Refreshing page...');
                                    window.location.reload();
                                } else {
                                    // If not paid yet, start polling
                                    if (window.startPaymentStatusPolling) {
                                        window.startPaymentStatusPolling();
                                    }
                                }
                            })
                            .catch(error => {
                                console.error('Payment status check failed on window close:', error);
                                if (window.startPaymentStatusPolling) {
                                    window.startPaymentStatusPolling();
                                }
                            });
                        }
                    } catch (e) {
                        console.log('Window check error:', e);
                    }
                }, 500);
                
                // Always start backup polling after 3 seconds
                setTimeout(function() {
                    if (!window.paymentPollingActive && window.startPaymentStatusPolling) {
                        console.log('Starting backup polling for manual open');
                        window.startPaymentStatusPolling();
                    }
                }, 3000);
            } else {
                // Pop-up was restricted by browser - automatically navigate in current window
                window.location.href = url;
            }
        } catch (e) {
            console.error('Error opening payment gateway manually:', e);
            window.location.href = url;
        }
    };
});
</script>
@endpush
@endsection
