<template>
  <div class="min-h-screen bg-slate-100 py-8 px-4 sm:px-6">
    <!-- Live Food & Payment Status Notification Toast -->
    <transition enter-active-class="transform transition ease-out duration-300" enter-from-class="translate-y-[-100%] opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div
        v-if="customerToast"
        :class="[
          'fixed top-4 left-1/2 -translate-x-1/2 z-50 text-white px-5 py-3.5 rounded-2xl shadow-2xl border-2 border-white/60 flex items-center gap-3 w-11/12 max-w-md',
          customerToast.color || 'bg-slate-900'
        ]"
      >
        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center font-bold text-lg shrink-0">
          <i :class="customerToast.icon || 'fa-solid fa-bell'"></i>
        </div>
        <div class="flex-1">
          <div class="font-black text-xs uppercase tracking-wider">{{ customerToast.title }}</div>
          <div class="text-[11px] font-medium text-white/95 mt-0.5">{{ customerToast.message }}</div>
        </div>
        <button @click="customerToast = null" class="w-7 h-7 rounded-full bg-black/20 hover:bg-black/30 text-white flex items-center justify-center transition cursor-pointer shrink-0">
          <i class="fa-solid fa-xmark text-xs"></i>
        </button>
      </div>
    </transition>
    <div v-if="loading" class="text-center py-20">
      <i class="fa-solid fa-circle-notch fa-spin text-4xl text-amber-500 mb-3"></i>
      <p class="text-slate-500 font-medium text-sm">Fetching order details...</p>
    </div>

    <div v-else-if="!order" class="max-w-md mx-auto bg-white p-8 rounded-3xl text-center shadow-lg">
      <i class="fa-solid fa-receipt text-5xl text-rose-500 mb-4"></i>
      <h2 class="text-xl font-bold text-slate-800 mb-2">
        {{ loadErrorCode === 404 ? 'Order Not Found' : 'Something Went Wrong' }}
      </h2>
      <p class="text-xs text-slate-500 mb-6">
        {{ loadErrorCode === 404 ? 'We could not locate this order code.' : 'Unable to load order details right now. Please retry.' }}
      </p>
      <div class="flex items-center justify-center gap-3">
        <button
          v-if="loadErrorCode !== 404"
          @click="fetchOrder"
          class="px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl text-xs cursor-pointer flex items-center gap-1.5"
        >
          <i class="fa-solid fa-arrows-rotate"></i>
          <span>Retry</span>
        </button>
        <router-link to="/" class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold rounded-xl text-xs">
          Return Home
        </router-link>
      </div>
    </div>

    <div v-else class="max-w-xl mx-auto space-y-6">
      <!-- Order Header Card -->
      <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-start justify-between border-b border-slate-100 pb-4 mb-4 gap-3">
          <div>
            <div class="flex items-center gap-2 flex-wrap">
              <span
                v-if="order.token_number || order.restaurant?.business_type === 'salon'"
                class="text-xs font-black uppercase tracking-wider text-cyan-900 bg-cyan-100 px-3 py-1 rounded-full border border-cyan-300 shadow-xs flex items-center gap-1.5"
              >
                <i class="fa-solid fa-ticket text-cyan-700"></i> TOKEN #{{ order.token_number || 1 }}
              </span>
              <span class="text-[11px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2.5 py-1 rounded-full border border-amber-200">
                #{{ order.order_code }}
              </span>
            </div>

            <h1 class="text-xl font-extrabold text-slate-900 mt-2">{{ order.restaurant?.name }}</h1>

            <!-- Salon / Appointment details -->
            <div v-if="order.restaurant?.business_type === 'salon' || order.booking_time" class="text-xs text-slate-600 mt-1 flex flex-wrap items-center gap-x-2 gap-y-1">
              <span class="font-bold text-cyan-700">
                <i class="fa-solid fa-calendar-day mr-1"></i> {{ order.booking_date || 'Today' }} at {{ order.booking_time || 'Immediate' }}
              </span>
              <span>•</span>
              <span><i class="fa-solid fa-chair text-slate-400 mr-1"></i> {{ order.chair_or_specialist || order.table_number }}</span>
              <span>•</span>
              <span class="text-slate-400">Booked at {{ formatDate(order.created_at) }}</span>
            </div>

            <p v-else class="text-xs text-slate-500 mt-1">{{ order.table_number }} • Placed at {{ formatDate(order.created_at) }}</p>
          </div>

          <!-- Order Status Badge & Live Alert Pill -->
          <div class="text-right shrink-0 flex flex-col items-end gap-1.5">
            <span
              :class="[
                'text-xs font-extrabold px-3 py-1.5 rounded-full capitalize inline-flex items-center gap-1.5',
                getStatusBadge(order.order_status)
              ]"
            >
              <i class="fa-solid fa-circle text-[8px] animate-pulse"></i>
              {{ order.order_status }}
            </span>

            <button
              @click="enableCustomerAlerts"
              class="text-[10px] font-bold px-2.5 py-1 rounded-xl border flex items-center gap-1 transition cursor-pointer"
              :class="customerAlertsEnabled ? 'bg-emerald-50 text-emerald-700 border-emerald-300' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'"
              :title="customerAlertsEnabled ? 'Live Sound & Status Alerts Active' : 'Enable sound alerts for cooking, ready, and payment status'"
            >
              <i :class="customerAlertsEnabled ? 'fa-solid fa-bell text-emerald-600' : 'fa-regular fa-bell text-slate-500'"></i>
              <span>{{ customerAlertsEnabled ? 'Alerts ON' : 'Enable Alerts' }}</span>
            </button>
          </div>
        </div>

        <!-- Order / Appointment Progress Bar -->
        <div class="py-2">
          <div v-if="order.restaurant?.business_type === 'salon'" class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold text-slate-500 mb-2">
            <span :class="isStepActive(1) ? 'text-cyan-700' : ''">1. Booked</span>
            <span :class="isStepActive(2) ? 'text-cyan-700' : ''">2. Next Up</span>
            <span :class="isStepActive(3) ? 'text-cyan-700' : ''">3. In Chair</span>
            <span :class="isStepActive(4) ? 'text-emerald-600' : ''">4. Completed</span>
          </div>

          <div v-else class="grid grid-cols-4 gap-2 text-center text-[10px] font-bold text-slate-500 mb-2">
            <span :class="isStepActive(1) ? 'text-amber-600' : ''">1. Received</span>
            <span :class="isStepActive(2) ? 'text-amber-600' : ''">2. Preparing</span>
            <span :class="isStepActive(3) ? 'text-amber-600' : ''">3. Ready</span>
            <span :class="isStepActive(4) ? 'text-emerald-600' : ''">4. Served</span>
          </div>

          <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden flex">
            <div
              class="bg-gradient-to-r from-amber-500 to-orange-500 h-full transition-all duration-700 rounded-full"
              :style="{ width: progressPercent + '%' }"
            ></div>
          </div>
        </div>
      </div>

      <!-- Royalty Visit Stamp Card (Loyalty Rewards Program) -->
      <div v-if="loyaltyProgress" class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-yellow-500/10 border-2 border-amber-300 rounded-3xl p-5 shadow-sm">
        <div class="flex items-center justify-between gap-2">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-amber-400 to-orange-500 text-slate-950 flex items-center justify-center text-lg font-black shadow shrink-0">
              <i class="fa-solid fa-crown"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-black text-slate-900">Royalty Stamp Recorded!</span>
                <span class="text-[9px] font-black px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 uppercase">VISIT #{{ loyaltyProgress.visit_count }}</span>
              </div>
              <p class="text-[11px] text-slate-600 mt-0.5">
                <template v-if="loyaltyProgress.can_play">
                  🎉 Milestone Reached! You have unlocked <strong>{{ loyaltyProgress.available_tickets }} Lucky Reward Ticket</strong>!
                </template>
                <template v-else>
                  <strong>{{ loyaltyProgress.visits_remaining }}</strong> more visit(s) needed to unlock your Lucky Spinner / Scratch Card ticket!
                </template>
              </p>
            </div>
          </div>

          <div class="shrink-0">
            <span class="text-xs font-extrabold text-amber-800 bg-amber-100 border border-amber-300 px-3 py-1 rounded-full">
              {{ loyaltyProgress.visit_count }} / {{ loyaltyProgress.required_visits }} Stamps
            </span>
          </div>
        </div>

        <!-- Stamp Progress Dots -->
        <div class="grid grid-cols-5 gap-2 mt-3.5">
          <div
            v-for="st in loyaltyProgress.required_visits"
            :key="st"
            :class="[
              'py-1.5 rounded-xl text-center text-[10px] font-black border transition flex items-center justify-center gap-1',
              st <= ((loyaltyProgress.visit_count % loyaltyProgress.required_visits === 0 && loyaltyProgress.visit_count > 0) ? loyaltyProgress.required_visits : (loyaltyProgress.visit_count % loyaltyProgress.required_visits))
                ? 'bg-amber-400 text-slate-950 border-amber-500 shadow-xs'
                : 'bg-white/80 border-dashed border-slate-300 text-slate-400'
            ]"
          >
            <i v-if="st <= ((loyaltyProgress.visit_count % loyaltyProgress.required_visits === 0 && loyaltyProgress.visit_count > 0) ? loyaltyProgress.required_visits : (loyaltyProgress.visit_count % loyaltyProgress.required_visits))" class="fa-solid fa-check text-[9px]"></i>
            <span>#{{ st }}</span>
          </div>
        </div>
      </div>

      <!-- Mind Game Waiting Banner Card (Play & Win 5% Discount) -->
      <div class="relative overflow-hidden bg-gradient-to-r from-violet-900 via-indigo-900 to-slate-900 rounded-3xl p-5 shadow-lg border border-indigo-500/30 text-white">
        <!-- Glow / Background decoration -->
        <div class="absolute -right-8 -bottom-8 w-36 h-36 bg-amber-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -left-8 -top-8 w-36 h-36 bg-violet-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="relative z-10">
          <div class="flex items-start justify-between gap-3">
            <div class="flex items-center gap-2">
              <span class="w-9 h-9 rounded-2xl bg-amber-400/20 border border-amber-400/40 flex items-center justify-center text-xl shadow-inner">
                🧠
              </span>
              <div>
                <span class="text-[10px] uppercase font-black tracking-widest text-amber-400 block">
                  Interactive 2-Game Tournament
                </span>
                <h2 class="text-sm sm:text-base font-extrabold text-white leading-tight">
                  Waiting for your {{ order.restaurant?.business_type === 'salon' ? 'turn' : 'order' }}? Win 2 Games & Get 5% OFF!
                </h2>
              </div>
            </div>

            <!-- Reward Status Badge -->
            <span
              v-if="order.game_reward_claimed"
              class="shrink-0 bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 text-[10px] font-extrabold px-2.5 py-1 rounded-full flex items-center gap-1"
            >
              <i class="fa-solid fa-trophy text-amber-400"></i> 5% Discount Applied
            </span>
            <span
              v-else
              class="shrink-0 bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[10px] font-extrabold px-2.5 py-1 rounded-full animate-pulse flex items-center gap-1"
            >
              <i class="fa-solid fa-bolt text-amber-400"></i> Win 2 Games (5% OFF)
            </span>
          </div>

          <p class="text-xs text-indigo-200/90 mt-2.5 leading-relaxed">
            Pass your waiting time with our 2-game challenge! Win <strong>Game 1 (Triple Match)</strong> and <strong>Game 2 (Speed Reflex)</strong> to unlock an instant <strong>5% discount</strong> directly on your bill.
          </p>

          <div class="mt-4 flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-indigo-700/50">
            <div class="flex items-center gap-3 text-[11px] text-indigo-300">
              <span class="flex items-center gap-1 font-bold text-amber-300"><i class="fa-solid fa-trophy text-amber-400"></i> 2 Games Required</span>
              <span class="flex items-center gap-1 font-bold text-cyan-300"><i class="fa-solid fa-layer-group text-cyan-400"></i> 1. Triple Match</span>
              <span class="flex items-center gap-1 font-bold text-emerald-300"><i class="fa-solid fa-bolt text-emerald-400"></i> 2. Speed Reflex</span>
              <span v-if="order.discount_amount > 0" class="text-emerald-400 font-bold">
                Saved {{ order.restaurant?.currency }}{{ order.discount_amount }}!
              </span>
            </div>

            <button
              @click="openGameModal"
              class="px-4 py-2 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-400 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg transition transform active:scale-95 flex items-center gap-1.5 cursor-pointer"
            >
              <i class="fa-solid fa-gamepad"></i>
              <span>{{ order.game_reward_claimed ? 'Play 2 Games Again' : 'Play 2 Games & Win 5% OFF' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Payment Section -->
      <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
            <i class="fa-solid fa-wallet text-amber-500"></i> Payment Details
          </h2>

          <span
            :class="[
              'text-xs font-extrabold px-3 py-1 rounded-full capitalize',
              order.payment_status === 'paid'
                ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                : 'bg-amber-100 text-amber-800 border border-amber-300'
            ]"
          >
            {{ order.payment_status === 'paid' ? '✓ Paid' : 'Payment Pending' }}
          </span>
        </div>

        <!-- If Paid -->
        <div v-if="order.payment_status === 'paid'" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-center">
          <i class="fa-solid fa-circle-check text-4xl text-emerald-600 mb-2"></i>
          <h3 class="font-extrabold text-emerald-900 text-sm">Payment Confirmed</h3>
          <p class="text-xs text-emerald-700 mt-0.5">
            Transaction ID: <span class="font-mono font-bold">{{ order.transaction_id || 'TXN-SUCCESS' }}</span>
          </p>
          <p class="text-[11px] text-emerald-600 mt-2">
            Method: <span class="font-bold uppercase">{{ order.payment_method }}</span> • Amount: {{ order.restaurant?.currency }}{{ order.total_amount }}
          </p>
        </div>

        <!-- If Payment is Pending -->
        <div v-else class="space-y-4">
          <!-- UPI Payment Flow -->
          <div v-if="order.payment_method === 'upi' && order.restaurant?.enable_online_payment !== false" class="bg-gradient-to-br from-amber-50 to-orange-50/50 rounded-2xl p-4 border border-amber-200 text-center">
            <div class="text-xs text-slate-600 mb-1">Total Amount Payable:</div>
            <div class="text-3xl font-extrabold text-slate-900 mb-3">
              {{ order.restaurant?.currency }}{{ order.total_amount }}
            </div>

            <!-- UPI QR Code -->
            <div class="bg-white p-4 rounded-2xl shadow-sm inline-block border border-amber-200/80 mb-3">
              <img
                v-if="upiQrDataUrl"
                :src="upiQrDataUrl"
                alt="UPI Payment QR Code"
                class="w-44 h-44 mx-auto rounded-lg object-contain block"
              />
              <div v-else class="w-44 h-44 flex items-center justify-center text-slate-400 text-xs">
                <i class="fa-solid fa-circle-notch fa-spin text-2xl text-amber-500"></i>
              </div>
              <div class="text-[10px] text-slate-500 mt-2 font-mono">
                UPI ID: <span class="font-bold text-slate-800">{{ order.restaurant?.upi_id || 'merchant@upi' }}</span>
              </div>
            </div>

            <p class="text-xs text-slate-600 max-w-xs mx-auto mb-3">
              Scan this QR code using Google Pay, PhonePe, Paytm, or any UPI app to pay directly.
            </p>

            <!-- Direct UPI Link for Mobile -->
            <div v-if="upiString" class="mb-4">
              <a
                :href="upiString"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow transition"
              >
                <i class="fa-solid fa-mobile-screen"></i> Pay Directly via UPI App
              </a>
            </div>

            <!-- Instant Verification Simulator / Ref Input -->
            <div class="border-t border-amber-200/80 pt-3 mt-2">
              <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                Have you completed the payment? Click below to confirm:
              </label>
              <div class="flex gap-2 max-w-sm mx-auto">
                <input
                  v-model="txnRef"
                  type="text"
                  placeholder="UPI Ref / UTR No. (Optional)"
                  class="flex-1 px-3 py-2 bg-white rounded-xl text-xs border border-slate-300 focus:outline-none"
                />
                <button
                  @click="confirmPayment"
                  :disabled="verifyingPayment"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition shrink-0"
                >
                  <i v-if="verifyingPayment" class="fa-solid fa-circle-notch fa-spin mr-1"></i>
                  Mark as Paid
                </button>
              </div>
            </div>
          </div>

          <!-- Cash / Pay at Counter Option -->
          <div v-else class="bg-slate-50 rounded-2xl p-4 border border-slate-200 text-center">
            <i class="fa-solid fa-hand-holding-dollar text-3xl text-emerald-600 mb-2"></i>
            <h3 class="font-bold text-slate-800 text-sm">Pay at Counter</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto mt-1">
              Please pay {{ order.restaurant?.currency }}{{ order.total_amount }} at the counter or to your server in cash/card.
            </p>
          </div>
        </div>
      </div>

      <!-- Ordered Items Receipt Card -->
      <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
        <h2 class="text-base font-bold text-slate-900 mb-3 flex items-center gap-2">
          <i class="fa-solid fa-utensils text-amber-500"></i> Items Ordered ({{ order.items?.length }})
        </h2>

        <div class="divide-y divide-slate-100">
          <div
            v-for="item in order.items"
            :key="item.id"
            class="py-2.5 flex items-center justify-between text-xs"
          >
            <div>
              <span class="font-bold text-slate-800">{{ item.item_name }}</span>
              <span class="text-slate-400 block text-[11px]">
                {{ order.restaurant?.currency }}{{ item.item_price }} x {{ item.quantity }}
              </span>
            </div>
            <span class="font-extrabold text-slate-900">
              {{ order.restaurant?.currency }}{{ item.subtotal }}
            </span>
          </div>
        </div>

        <div class="border-t border-slate-200 mt-4 pt-3 space-y-1.5 text-xs">
          <div class="flex justify-between text-slate-500">
            <span>Subtotal:</span>
            <span>{{ order.restaurant?.currency }}{{ order.subtotal }}</span>
          </div>

          <!-- Mind Game Winner 5% Discount Row -->
          <div v-if="order.discount_amount > 0" class="flex justify-between text-emerald-600 font-bold bg-emerald-50 px-2.5 py-1.5 rounded-xl border border-emerald-200">
            <span class="flex items-center gap-1.5">
              <i class="fa-solid fa-trophy text-amber-500"></i> Mind Game Winner (5% OFF):
            </span>
            <span>-{{ order.restaurant?.currency }}{{ order.discount_amount }}</span>
          </div>

          <!-- Royalty Reward Discount Row -->
          <div v-if="order.loyalty_reward_claimed && order.loyalty_reward_details" class="flex justify-between text-amber-700 font-bold bg-amber-50 px-2.5 py-1.5 rounded-xl border border-amber-300">
            <span class="flex items-center gap-1.5">
              <i class="fa-solid fa-crown text-amber-500"></i> Royalty Reward ({{ order.loyalty_reward_details.title || order.loyalty_reward_details.reward_title || 'Loyalty Discount' }}):
            </span>
            <span>-{{ order.restaurant?.currency }}{{ order.discount_amount }}</span>
          </div>

          <div class="flex justify-between text-slate-500">
            <span>Taxes ({{ order.restaurant?.tax_percentage || 0 }}%):</span>
            <span>{{ order.restaurant?.currency }}{{ order.tax_amount }}</span>
          </div>
          <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-slate-100">
            <span>Grand Total:</span>
            <span class="text-amber-600">{{ order.restaurant?.currency }}{{ order.total_amount }}</span>
          </div>
        </div>

        <div v-if="order.special_notes" class="mt-4 p-3 bg-amber-50/60 rounded-xl border border-amber-200/50 text-xs text-amber-900">
          <span class="font-bold">Kitchen Note:</span> {{ order.special_notes }}
        </div>
      </div>

      <!-- WhatsApp Digital Bill & Split Bill Actions Row -->
      <div v-if="order.restaurant?.enable_whatsapp_bill || order.restaurant?.enable_split_bill" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <!-- WhatsApp Share Bill Button -->
        <button
          v-if="order.restaurant?.enable_whatsapp_bill"
          @click="shareBillOnWhatsApp"
          class="py-3 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-2xl shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <i class="fa-brands fa-whatsapp text-lg"></i>
          <span>Share Bill on WhatsApp</span>
        </button>

        <!-- Split Bill with Friends Button -->
        <button
          v-if="order.restaurant?.enable_split_bill"
          @click="openSplitBillModal"
          class="py-3 px-4 bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs rounded-2xl shadow-sm transition flex items-center justify-center gap-2 cursor-pointer"
        >
          <i class="fa-solid fa-users text-sm"></i>
          <span>Split Bill with Friends</span>
        </button>
      </div>

      <!-- Smart Google Reviews & Feedback Booster Card -->
      <div
        v-if="order.restaurant?.enable_google_reviews"
        class="bg-white rounded-3xl border border-slate-200 p-5 shadow-sm space-y-3"
      >
        <div class="flex items-center gap-2.5">
          <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-sm font-black">
            ⭐
          </div>
          <div>
            <h4 class="font-black text-sm text-slate-900">How was your dining experience?</h4>
            <p class="text-[11px] text-slate-500">Your rating helps us maintain top quality food & service</p>
          </div>
        </div>

        <!-- Rating Submitted Thanks State -->
        <div v-if="feedbackSubmitted" class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-center space-y-1">
          <div class="text-xl">🎉</div>
          <div class="text-xs font-black text-emerald-900">{{ feedbackResponseMsg }}</div>
          <a
            v-if="feedbackRedirectUrl"
            :href="feedbackRedirectUrl"
            target="_blank"
            class="inline-flex items-center gap-1.5 px-4 py-2 mt-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-xs rounded-xl shadow transition"
          >
            <i class="fa-brands fa-google"></i>
            <span>Post on Google Maps ⭐⭐⭐⭐⭐</span>
          </a>
        </div>

        <!-- Rating Star Selector -->
        <div v-else class="space-y-3 pt-1">
          <div class="flex justify-center items-center gap-2 py-2">
            <button
              v-for="star in 5"
              :key="star"
              type="button"
              @click="selectedRating = star"
              class="text-3xl sm:text-4xl transition transform hover:scale-125 active:scale-95 cursor-pointer focus:outline-none"
              :class="star <= selectedRating ? 'text-amber-400 drop-shadow' : 'text-slate-200'"
            >
              ★
            </button>
          </div>

          <!-- If rated 4 or 5 stars (Booster redirect prompt) -->
          <div v-if="selectedRating >= 4" class="p-3 bg-amber-50 border border-amber-200 rounded-2xl text-center space-y-2">
            <p class="text-xs font-bold text-amber-900">
              You rated us {{ selectedRating }} Stars! We're thrilled you loved your meal! ❤️
            </p>
            <p class="text-[11px] text-amber-700">
              A quick review on Google Maps helps others discover our kitchen!
            </p>
            <button
              @click="submitFeedback"
              :disabled="submittingFeedback"
              class="w-full py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-xs rounded-xl shadow transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <i v-if="submittingFeedback" class="fa-solid fa-circle-notch fa-spin"></i>
              <span v-else>Submit & Share on Google Maps ⭐</span>
            </button>
          </div>

          <!-- If rated 1 to 3 stars (Private manager feedback protection) -->
          <div v-else-if="selectedRating > 0" class="p-3 bg-slate-50 border border-slate-200 rounded-2xl space-y-2">
            <p class="text-xs font-bold text-slate-800">
              We're sorry we fell short! Please tell our manager what went wrong:
            </p>
            <textarea
              v-model="feedbackComment"
              rows="2"
              placeholder="Tell us about the taste, service, or cleanliness..."
              class="w-full p-2.5 bg-white border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
            ></textarea>
            <button
              @click="submitFeedback"
              :disabled="submittingFeedback"
              class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-2 cursor-pointer"
            >
              <i v-if="submittingFeedback" class="fa-solid fa-circle-notch fa-spin"></i>
              <span v-else>Submit Private Feedback to Manager</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Action Navigation Buttons -->
      <div class="flex gap-3">
        <router-link
          :to="`/r/${order.restaurant?.slug}?table=${encodeURIComponent(order.table_number || 'Table 1')}&continue_order=${encodeURIComponent(order.order_code || '')}`"
          class="flex-1 py-3 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-2xl border border-slate-200 text-center shadow-sm"
        >
          <i class="fa-solid fa-plus mr-1"></i> Order More Items
        </router-link>
        <button
          @click="fetchOrder"
          class="px-4 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-2xl text-center shadow-sm"
          title="Refresh Live Status"
        >
          <i class="fa-solid fa-arrows-rotate mr-1"></i> Refresh Status
        </button>
      </div>
    </div>

    <!-- 2-Game Tournament Modal (Game 1: Triple Match + Game 2: Speed Reflex) -->
    <div
      v-if="showGameModal"
      class="fixed inset-0 z-50 bg-slate-950/85 backdrop-blur-md flex items-center justify-center p-3 sm:p-4"
    >
      <div class="bg-slate-900 border border-slate-700/80 w-full max-w-md rounded-3xl p-4 sm:p-5 shadow-2xl text-white relative overflow-hidden">
        <!-- Floating glow dots -->
        <div class="absolute -top-12 -left-12 w-32 h-32 bg-purple-500/20 rounded-full blur-2xl pointer-events-none"></div>
        <div class="absolute -bottom-12 -right-12 w-32 h-32 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

        <!-- Header -->
        <div class="flex items-center justify-between pb-2.5 border-b border-slate-800 mb-3 relative z-10">
          <div class="flex items-center gap-2">
            <span class="text-xl">🏆</span>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-black text-sm text-white">2-Game Challenge</h3>
                <span class="text-[9px] uppercase font-black px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                  Win 2 = 5% OFF
                </span>
              </div>
              <p class="text-[10px] text-slate-400">Win both games to unlock 5% discount on bill!</p>
            </div>
          </div>

          <button
            @click="closeGameModal"
            class="text-slate-400 hover:text-white p-1 rounded-lg hover:bg-slate-800 transition cursor-pointer"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <!-- 2-Game Progress Stepper -->
        <div class="grid grid-cols-2 gap-2 mb-3 relative z-10 text-xs font-bold">
          <div
            :class="[
              'p-2 rounded-xl border flex items-center gap-2 transition',
              game1Won
                ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300'
                : activeGame === 1
                ? 'bg-amber-500/15 border-amber-500/40 text-amber-300'
                : 'bg-slate-800/60 border-slate-700 text-slate-400'
            ]"
          >
            <span
              :class="game1Won ? 'bg-emerald-500 text-slate-950' : 'bg-slate-700 text-white'"
              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
            >
              {{ game1Won ? '✓' : '1' }}
            </span>
            <span class="truncate text-[11px]">1. Triple Match</span>
          </div>

          <div
            :class="[
              'p-2 rounded-xl border flex items-center gap-2 transition',
              game2Won
                ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-300'
                : activeGame === 2
                ? 'bg-amber-500/15 border-amber-500/40 text-amber-300'
                : 'bg-slate-800/60 border-slate-700 text-slate-400'
            ]"
          >
            <span
              :class="game2Won ? 'bg-emerald-500 text-slate-950' : 'bg-slate-700 text-white'"
              class="w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-black shrink-0"
            >
              {{ game2Won ? '✓' : '2' }}
            </span>
            <span class="truncate text-[11px]">2. Speed Reflex</span>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- VIEW 1: GAME 1 (TRIPLE MATCH) ACTIVE           -->
        <!-- ============================================== -->
        <div v-if="activeGame === 1 && gameState === 'playing'" class="space-y-3 relative z-10">
          <!-- Stats Header -->
          <div class="grid grid-cols-3 gap-2 text-center">
            <div class="bg-slate-800/80 rounded-xl p-2 border border-slate-700/60">
              <span class="text-[9px] uppercase font-bold text-slate-400 block">Time Left</span>
              <span
                :class="timeRemaining <= 10 ? 'text-rose-400 animate-pulse font-extrabold' : 'text-amber-400 font-black'"
                class="text-base font-mono"
              >
                {{ timeRemaining }}s
              </span>
            </div>

            <div class="bg-slate-800/80 rounded-xl p-2 border border-slate-700/60">
              <span class="text-[9px] uppercase font-bold text-slate-400 block">Triplets</span>
              <span class="font-black text-base text-emerald-400 font-mono">{{ matchedTripletsCount }} / 4</span>
            </div>

            <div class="bg-slate-800/80 rounded-xl p-2 border border-slate-700/60">
              <span class="text-[9px] uppercase font-bold text-slate-400 block">Attempts</span>
              <span class="font-black text-base text-cyan-400 font-mono">{{ movesCount }}</span>
            </div>
          </div>

          <div class="py-1 px-3 bg-indigo-950/70 border border-indigo-500/30 rounded-xl text-center text-[11px] text-indigo-300 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-layer-group text-amber-400"></i>
            <span>Game 1: Ek sath <strong>3 matching cards</strong> tap karke set banayein!</span>
          </div>

          <!-- 12 Cards Grid (4 columns x 3 rows) -->
          <div class="grid grid-cols-4 gap-2.5">
            <button
              v-for="(card, index) in cards"
              :key="index"
              @click="onCardClick(index)"
              :disabled="card.isFlipped || card.isMatched"
              :class="[
                'w-full aspect-square rounded-2xl flex items-center justify-center transition-all duration-200 font-bold select-none cursor-pointer',
                card.isMatched
                  ? 'bg-gradient-to-br from-emerald-500 to-teal-600 border border-emerald-300/80 text-2xl sm:text-3xl shadow-md scale-95 opacity-80'
                  : card.isFlipped
                  ? 'bg-gradient-to-br from-amber-400 to-orange-500 border border-white text-2xl sm:text-3xl shadow-lg scale-105'
                  : 'bg-gradient-to-br from-slate-800 to-indigo-950/90 border border-indigo-500/30 hover:border-amber-400 hover:scale-105 text-indigo-300 text-sm shadow-sm'
              ]"
            >
              <span v-if="card.isFlipped || card.isMatched" class="filter drop-shadow">{{ card.icon }}</span>
              <span v-else class="text-xs font-black text-indigo-400/60">?</span>
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- VIEW 2: GAME 1 WON -> TRANSITION TO GAME 2     -->
        <!-- ============================================== -->
        <div v-else-if="gameState === 'game1_won'" class="text-center py-5 space-y-4 relative z-10">
          <div class="w-16 h-16 mx-auto rounded-3xl bg-cyan-500/20 border-2 border-cyan-400/50 flex items-center justify-center text-4xl shadow-xl animate-bounce">
            🎯
          </div>

          <div>
            <span class="text-xs font-black uppercase tracking-wider text-cyan-400 block">Round 1 Cleared! (1 of 2)</span>
            <h4 class="text-lg sm:text-xl font-extrabold text-white mt-1">
              Triple Match Won! 🎉
            </h4>
            <p class="text-xs text-slate-300 max-w-xs mx-auto mt-1">
              You cleared Round 1! Now win <strong>Game 2 (Speed Reflex)</strong> to unlock your 5% discount!
            </p>
          </div>

          <div class="bg-slate-800/80 border border-slate-700 rounded-2xl p-3 text-xs text-slate-300">
            <strong class="text-amber-400 block mb-0.5">Game 2 Task:</strong>
            Tap 10 targets in 20 seconds before they move!
          </div>

          <button
            @click="startReflexGame"
            class="w-full py-3 bg-gradient-to-r from-cyan-500 via-teal-500 to-emerald-400 hover:from-cyan-400 hover:to-emerald-300 text-slate-950 font-black rounded-xl text-xs shadow-lg transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <span>Start Game 2 (Speed Reflex)</span>
            <i class="fa-solid fa-arrow-right"></i>
          </button>
        </div>

        <!-- ============================================== -->
        <!-- VIEW 3: GAME 2 (SPEED REFLEX TAP) ACTIVE       -->
        <!-- ============================================== -->
        <div v-else-if="activeGame === 2 && gameState === 'playing'" class="space-y-3 relative z-10">
          <!-- Stats Header -->
          <div class="grid grid-cols-2 gap-2 text-center">
            <div class="bg-slate-800/80 rounded-xl p-2 border border-slate-700/60">
              <span class="text-[9px] uppercase font-bold text-slate-400 block">Time Left</span>
              <span
                :class="reflexTimeRemaining <= 5 ? 'text-rose-400 animate-pulse font-extrabold' : 'text-amber-400 font-black'"
                class="text-base font-mono"
              >
                {{ reflexTimeRemaining }}s
              </span>
            </div>

            <div class="bg-slate-800/80 rounded-xl p-2 border border-slate-700/60">
              <span class="text-[9px] uppercase font-bold text-slate-400 block">Targets Tapped</span>
              <span class="font-black text-base text-emerald-400 font-mono">{{ reflexScore }} / 10</span>
            </div>
          </div>

          <div class="py-1 px-3 bg-teal-950/70 border border-teal-500/30 rounded-xl text-center text-[11px] text-teal-300 flex items-center justify-center gap-1.5">
            <i class="fa-solid fa-bolt text-amber-400"></i>
            <span>Tap the jumping targets! Hit 10 targets before time runs out.</span>
          </div>

          <!-- 3x3 Reflex Board (9 spots) -->
          <div class="grid grid-cols-3 gap-3 p-2 bg-slate-950/60 rounded-3xl border border-slate-800">
            <div
              v-for="spotIdx in 9"
              :key="spotIdx - 1"
              class="w-full aspect-square rounded-2xl bg-slate-800/60 border border-slate-700/50 flex items-center justify-center relative overflow-hidden"
            >
              <!-- Target active button -->
              <button
                v-if="activeReflexSpot === (spotIdx - 1)"
                @click="onReflexTap(spotIdx - 1)"
                class="w-full h-full bg-gradient-to-br from-amber-400 via-orange-500 to-amber-500 rounded-2xl shadow-lg flex items-center justify-center text-3xl sm:text-4xl animate-bounce-short active:scale-90 transition transform cursor-pointer border-2 border-white select-none"
              >
                {{ reflexEmoji }}
              </button>
            </div>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- VIEW 4: BOTH GAMES WON! GRAND CELEBRATION      -->
        <!-- ============================================== -->
        <div v-else-if="gameState === 'both_won'" class="text-center py-5 space-y-3 relative z-10">
          <div class="w-16 h-16 mx-auto rounded-3xl bg-amber-400/20 border-2 border-amber-400/50 flex items-center justify-center text-4xl shadow-xl animate-bounce">
            🏆
          </div>

          <div>
            <span class="text-xs font-black uppercase tracking-wider text-amber-400 block">Double Champion! (2 of 2 Won)</span>
            <h4 class="text-lg sm:text-xl font-extrabold text-white mt-1">
              You Won Both Games! 🎉
            </h4>
            <p class="text-xs text-slate-300 max-w-xs mx-auto mt-1">
              Both Triple Match and Speed Reflex cleared! A 5% discount has been applied to your live bill.
            </p>
          </div>

          <!-- Reward Box -->
          <div class="bg-gradient-to-br from-emerald-950/80 to-slate-900 border border-emerald-500/40 rounded-2xl p-4 text-center shadow-lg">
            <span class="text-[10px] uppercase font-bold text-emerald-400 tracking-wider block">
              Reward Unlocked
            </span>
            <div class="text-2xl font-black text-emerald-300 mt-0.5">
              5% Instant Discount
            </div>

            <div v-if="claimingReward" class="text-xs text-slate-400 mt-2 flex items-center justify-center gap-2">
              <i class="fa-solid fa-circle-notch fa-spin text-amber-400"></i> Applying discount to your order...
            </div>
            <div v-else class="mt-2 text-xs text-emerald-200">
              <span v-if="rewardDiscountAmount > 0" class="font-extrabold text-amber-300">
                Saved {{ order.restaurant?.currency }}{{ rewardDiscountAmount }}!
              </span>
              <span v-else>5% Discount successfully deducted from your bill!</span>
            </div>

            <div class="mt-3 bg-slate-900/90 py-1.5 px-3 rounded-xl border border-slate-700/60 inline-flex items-center gap-2 text-[11px] font-mono text-amber-400">
              <span>VOUCHER:</span>
              <strong class="text-white">WIN2-{{ order.order_code }}</strong>
            </div>
          </div>

          <div class="flex gap-2 pt-2">
            <button
              @click="initTournament"
              class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs transition cursor-pointer"
            >
              <i class="fa-solid fa-rotate-right mr-1"></i> Play Again
            </button>
            <button
              @click="closeGameModal"
              class="flex-1 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold rounded-xl text-xs shadow-lg transition cursor-pointer"
            >
              View Updated Bill
            </button>
          </div>
        </div>

        <!-- ============================================== -->
        <!-- VIEW 5: GAME OVER / TIMEOUT (GAME 1 OR GAME 2) -->
        <!-- ============================================== -->
        <div v-else-if="gameState === 'lost'" class="text-center py-6 space-y-3 relative z-10">
          <div class="w-16 h-16 mx-auto rounded-3xl bg-rose-500/20 border-2 border-rose-500/40 flex items-center justify-center text-4xl shadow-xl">
            ⏰
          </div>

          <div>
            <h4 class="text-lg font-extrabold text-white">Time's Up!</h4>
            <p v-if="activeGame === 1" class="text-xs text-slate-400 max-w-xs mx-auto mt-1">
              Time expired! You matched {{ matchedTripletsCount }} out of 4 triplets. Try again!
            </p>
            <p v-else class="text-xs text-slate-400 max-w-xs mx-auto mt-1">
              Time's up! You tapped {{ reflexScore }} out of 10 targets. Game 1 is saved, try Game 2 again!
            </p>
          </div>

          <button
            @click="activeGame === 1 ? initGame1() : startReflexGame()"
            class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition cursor-pointer"
          >
            <i class="fa-solid fa-rotate-right mr-1"></i> {{ activeGame === 1 ? 'Retry Game 1 (Triple Match)' : 'Retry Game 2 (Speed Reflex)' }}
          </button>
        </div>

        <!-- Footer Note -->
        <div class="text-center text-[10px] text-slate-500 border-t border-slate-800 pt-2 mt-2 relative z-10">
          🏆 Win Game 1 + Game 2 = Instant 5% Bill Discount
        </div>
      </div>
    </div>

    <!-- Split Bill with Friends Modal -->
    <div
      v-if="showSplitBillModal"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
      @click.self="showSplitBillModal = false"
    >
      <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl text-slate-900 border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-cyan-500/20 text-cyan-600 flex items-center justify-center text-base border border-cyan-500/30">
              <i class="fa-solid fa-users"></i>
            </div>
            <div>
              <h3 class="font-extrabold text-sm text-slate-900">Split Bill with Friends</h3>
              <p class="text-[11px] text-slate-500">Total Bill: <strong>{{ order.restaurant?.currency }}{{ order.total_amount }}</strong></p>
            </div>
          </div>
          <button
            @click="showSplitBillModal = false"
            class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer"
          >
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>

        <div class="space-y-4">
          <!-- Split Count Selector -->
          <div class="bg-slate-50 p-4 rounded-2xl border border-slate-200 text-center">
            <span class="text-xs font-semibold text-slate-600 block mb-2">Split between how many people?</span>
            <div class="flex items-center justify-center gap-4">
              <button
                @click="splitCount = Math.max(2, splitCount - 1); generateSplitQr();"
                class="w-10 h-10 rounded-xl bg-white border border-slate-300 text-slate-700 font-black text-lg flex items-center justify-center shadow-sm hover:bg-slate-100 cursor-pointer"
              >
                -
              </button>
              <div class="text-center min-w-[80px]">
                <span class="text-3xl font-black text-slate-900 font-mono">{{ splitCount }}</span>
                <span class="text-[10px] text-slate-500 block uppercase font-bold">Diners</span>
              </div>
              <button
                @click="splitCount = Math.min(10, splitCount + 1); generateSplitQr();"
                class="w-10 h-10 rounded-xl bg-white border border-slate-300 text-slate-700 font-black text-lg flex items-center justify-center shadow-sm hover:bg-slate-100 cursor-pointer"
              >
                +
              </button>
            </div>

            <!-- Per Person Amount Highlight -->
            <div class="mt-4 pt-3 border-t border-slate-200 flex items-center justify-between">
              <span class="text-xs font-bold text-slate-700">Each Person Pays:</span>
              <span class="text-xl font-black text-cyan-600 font-mono">
                {{ order.restaurant?.currency }}{{ perPersonAmount }}
              </span>
            </div>
          </div>

          <!-- Split UPI QR Code -->
          <div v-if="splitQrDataUrl" class="bg-slate-900 text-white p-4 rounded-2xl text-center space-y-2">
            <span class="text-[11px] font-bold text-slate-300 block">
              Scan to Pay Exact Share ({{ order.restaurant?.currency }}{{ perPersonAmount }})
            </span>
            <div class="w-40 h-40 bg-white p-2 rounded-2xl mx-auto shadow flex items-center justify-center">
              <img :src="splitQrDataUrl" class="w-full h-full object-contain" />
            </div>
            <div v-if="splitUpiString" class="pt-1">
              <a
                :href="splitUpiString"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-black text-xs rounded-xl shadow transition"
              >
                <i class="fa-solid fa-mobile-screen"></i> Pay {{ order.restaurant?.currency }}{{ perPersonAmount }} via UPI
              </a>
            </div>
          </div>

          <!-- Share on WhatsApp with Friends -->
          <button
            @click="shareSplitOnWhatsApp"
            class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-xl text-xs shadow transition flex items-center justify-center gap-2 cursor-pointer"
          >
            <i class="fa-brands fa-whatsapp text-base"></i>
            <span>Share {{ order.restaurant?.currency }}{{ perPersonAmount }} Share with Friends</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import QRCode from 'qrcode';
import { playSound, sendSystemNotification, requestNotificationPermission } from '../utils/notification';

const route = useRoute();
const order = ref(null);
const upiString = ref(null);
const upiQrDataUrl = ref('');
const loading = ref(true);
const txnRef = ref('');
const verifyingPayment = ref(false);
const loyaltyProgress = ref(null);
const loadErrorCode = ref(null);

// Live Notifications & Alerts State
const prevOrderStatus = ref(null);
const prevPaymentStatus = ref(null);
const customerToast = ref(null);
const customerAlertsEnabled = ref(false);
let toastTimer = null;

const enableCustomerAlerts = async () => {
  playSound('status_update');
  await requestNotificationPermission();
  customerAlertsEnabled.value = true;
};

const showToast = (toastObj) => {
  customerToast.value = toastObj;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    customerToast.value = null;
  }, 7000);
};

// --- WhatsApp Digital Bill Sharing ---
const shareBillOnWhatsApp = () => {
  if (!order.value) return;
  const o = order.value;
  const r = o.restaurant || {};
  const currency = r.currency || '₹';
  const tableOrType = o.table_number || (o.order_type === 'delivery' ? 'Home Delivery' : 'Takeaway');

  let itemLines = '';
  if (o.items && o.items.length > 0) {
    itemLines = o.items.map(i => `• ${i.quantity}x ${i.item_name} - ${currency}${i.subtotal}`).join('\n');
  }

  const message = `🧾 *Digital Receipt from ${r.name || 'Restaurant'}*
--------------------------------
*Order Code:* #${o.order_code}
*Date:* ${new Date(o.created_at).toLocaleDateString()}
*Table / Type:* ${tableOrType}
*Customer:* ${o.customer_name || 'Guest'}
--------------------------------
${itemLines}
--------------------------------
*Subtotal:* ${currency}${o.subtotal}
${o.discount_amount > 0 ? `*Discount:* -${currency}${o.discount_amount}\n` : ''}*Taxes:* ${currency}${o.tax_amount}
*GRAND TOTAL:* ${currency}${o.total_amount}
*Payment Status:* ${o.payment_status?.toUpperCase()} (${o.payment_method?.toUpperCase()})
--------------------------------
Track order live: ${window.location.href}
Thank you for dining with us! 🙏`;

  const encoded = encodeURIComponent(message);
  window.open(`https://api.whatsapp.com/send?text=${encoded}`, '_blank');
};

// --- Split Bill with Friends ---
const showSplitBillModal = ref(false);
const splitCount = ref(2);
const splitQrDataUrl = ref('');

const perPersonAmount = computed(() => {
  if (!order.value || !order.value.total_amount) return '0.00';
  return (order.value.total_amount / splitCount.value).toFixed(2);
});

const splitUpiString = computed(() => {
  if (!order.value?.restaurant?.upi_id) return null;
  const upiId = order.value.restaurant.upi_id;
  const payee = encodeURIComponent(order.value.restaurant.upi_name || order.value.restaurant.name);
  const note = encodeURIComponent(`Split #${order.value.order_code} (${splitCount.value} ways)`);
  return `upi://pay?pa=${upiId}&pn=${payee}&am=${perPersonAmount.value}&cu=INR&tn=${note}`;
});

const generateSplitQr = async () => {
  if (!splitUpiString.value) return;
  try {
    splitQrDataUrl.value = await QRCode.toDataURL(splitUpiString.value, {
      width: 220,
      margin: 1,
      color: { dark: '#0f172a', light: '#ffffff' }
    });
  } catch (err) {
    console.error('Failed to generate split QR', err);
  }
};

const openSplitBillModal = () => {
  showSplitBillModal.value = true;
  generateSplitQr();
};

const shareSplitOnWhatsApp = () => {
  if (!order.value) return;
  const currency = order.value.restaurant?.currency || '₹';
  const text = `Hey friends! 🍽️
Our total bill at *${order.value.restaurant?.name || 'Restaurant'}* is *${currency}${order.value.total_amount}*.
Splitting equally between *${splitCount.value} people*:
👉 *Each person's share: ${currency}${perPersonAmount.value}*

Pay directly via UPI or view live bill:
${window.location.href}`;

  window.open(`https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`, '_blank');
};

// --- Smart Google Reviews & Feedback Booster ---
const selectedRating = ref(5);
const feedbackComment = ref('');
const submittingFeedback = ref(false);
const feedbackSubmitted = ref(false);
const feedbackResponseMsg = ref('');
const feedbackRedirectUrl = ref('');

const submitFeedback = async () => {
  if (!order.value) return;
  submittingFeedback.value = true;
  try {
    const res = await axios.post(`/r/${order.value.restaurant?.slug}/feedback`, {
      rating: selectedRating.value,
      comment: feedbackComment.value,
      order_code: order.value.order_code,
      customer_name: order.value.customer_name,
      customer_phone: order.value.customer_phone,
    });
    if (res.data.success) {
      feedbackSubmitted.value = true;
      feedbackResponseMsg.value = res.data.message;
      feedbackRedirectUrl.value = res.data.google_review_url || '';

      if (res.data.redirect_to_google && res.data.google_review_url) {
        window.open(res.data.google_review_url, '_blank');
      }
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit feedback');
  } finally {
    submittingFeedback.value = false;
  }
};

// --- 2-Game Tournament State ---
const showGameModal = ref(false);
const activeGame = ref(1); // 1 = Triple Match, 2 = Speed Reflex
const game1Won = ref(false);
const game2Won = ref(false);
const gameState = ref('playing'); // 'playing' | 'game1_won' | 'both_won' | 'lost'
const claimingReward = ref(false);
const rewardDiscountAmount = ref(0);

// Game 1: Triple Match (Match 3 Cards)
const cards = ref([]);
const flippedCards = ref([]);
const movesCount = ref(0);
const matchedTripletsCount = ref(0);
const timeRemaining = ref(45);
let gameTimer = null;

// Game 2: Speed Reflex Tap
const reflexScore = ref(0);
const reflexTargetScore = 10;
const reflexTimeRemaining = ref(20);
const activeReflexSpot = ref(null);
const reflexEmoji = ref('⭐');
let reflexTimer = null;
let reflexPopInterval = null;

const getCategoryIcons = () => {
  const bType = order.value?.restaurant?.business_type;
  if (bType === 'salon') {
    return ['💈', '✂️', '🪮', '🧴'];
  } else if (bType === 'retail') {
    return ['🛍️', '👕', '👟', '🕶️'];
  } else {
    return ['🍕', '🍔', '🍟', '🥤'];
  }
};

const openGameModal = () => {
  showGameModal.value = true;
  initTournament();
};

const closeGameModal = () => {
  showGameModal.value = false;
  clearAllGameTimers();
};

const clearAllGameTimers = () => {
  if (gameTimer) clearInterval(gameTimer);
  if (reflexTimer) clearInterval(reflexTimer);
  if (reflexPopInterval) clearInterval(reflexPopInterval);
};

const initTournament = () => {
  game1Won.value = false;
  game2Won.value = false;
  initGame1();
};

// --- GAME 1: TRIPLE MATCH ---
const initGame1 = () => {
  clearAllGameTimers();
  activeGame.value = 1;

  const baseIcons = getCategoryIcons();
  // 3 copies of each of the 4 items = 12 cards (4 Triplets)
  const triplets = [...baseIcons, ...baseIcons, ...baseIcons];

  cards.value = triplets
    .map((icon, id) => ({ id, icon, isFlipped: false, isMatched: false }))
    .sort(() => Math.random() - 0.5);

  flippedCards.value = [];
  movesCount.value = 0;
  matchedTripletsCount.value = 0;
  timeRemaining.value = 45;
  gameState.value = 'playing';

  gameTimer = setInterval(() => {
    if (timeRemaining.value > 0) {
      timeRemaining.value--;
    } else {
      clearInterval(gameTimer);
      if (matchedTripletsCount.value < 4 && gameState.value === 'playing') {
        gameState.value = 'lost';
      }
    }
  }, 1000);
};

const onCardClick = (index) => {
  if (gameState.value !== 'playing' || activeGame.value !== 1) return;
  const card = cards.value[index];
  if (card.isFlipped || card.isMatched) return;
  if (flippedCards.value.length >= 3) return;

  card.isFlipped = true;
  flippedCards.value.push(index);

  if (flippedCards.value.length === 3) {
    movesCount.value++;
    const [firstIdx, secondIdx, thirdIdx] = flippedCards.value;
    const card1 = cards.value[firstIdx];
    const card2 = cards.value[secondIdx];
    const card3 = cards.value[thirdIdx];

    if (card1.icon === card2.icon && card2.icon === card3.icon) {
      card1.isMatched = true;
      card2.isMatched = true;
      card3.isMatched = true;
      flippedCards.value = [];
      matchedTripletsCount.value++;

      if (matchedTripletsCount.value === 4) {
        clearInterval(gameTimer);
        game1Won.value = true;
        gameState.value = 'game1_won';
      }
    } else {
      setTimeout(() => {
        card1.isFlipped = false;
        card2.isFlipped = false;
        card3.isFlipped = false;
        flippedCards.value = [];
      }, 600);
    }
  }
};

// --- GAME 2: SPEED REFLEX TAP ---
const startReflexGame = () => {
  clearAllGameTimers();
  activeGame.value = 2;
  gameState.value = 'playing';
  reflexScore.value = 0;
  reflexTimeRemaining.value = 20;

  popNextReflexSpot();
  reflexPopInterval = setInterval(popNextReflexSpot, 850);

  reflexTimer = setInterval(() => {
    if (reflexTimeRemaining.value > 0) {
      reflexTimeRemaining.value--;
    } else {
      clearAllGameTimers();
      if (reflexScore.value < 10 && gameState.value === 'playing') {
        gameState.value = 'lost';
      }
    }
  }, 1000);
};

const popNextReflexSpot = () => {
  const baseIcons = getCategoryIcons();
  reflexEmoji.value = baseIcons[Math.floor(Math.random() * baseIcons.length)];

  let nextSpot;
  do {
    nextSpot = Math.floor(Math.random() * 9);
  } while (nextSpot === activeReflexSpot.value);

  activeReflexSpot.value = nextSpot;
};

const onReflexTap = (index) => {
  if (gameState.value !== 'playing' || activeGame.value !== 2) return;
  if (activeReflexSpot.value === index) {
    reflexScore.value++;
    activeReflexSpot.value = null;

    if (reflexScore.value >= reflexTargetScore) {
      clearAllGameTimers();
      game2Won.value = true;
      gameState.value = 'both_won';
      handleGameVictory();
    } else {
      popNextReflexSpot();
    }
  }
};

const handleGameVictory = async () => {
  if (!order.value?.game_reward_claimed) {
    claimingReward.value = true;
    try {
      const code = route.params.orderCode;
      const res = await axios.post(`/orders/${code}/apply-game-reward`);
      if (res.data.success) {
        order.value = res.data.order;
        upiString.value = res.data.upi_string;
        rewardDiscountAmount.value = res.data.discount_amount;
        if (upiString.value && order.value.payment_status !== 'paid') {
          renderQr();
        }
      }
    } catch (err) {
      console.error('Failed to claim game discount', err);
    } finally {
      claimingReward.value = false;
    }
  }
};

const fetchOrder = async () => {
  try {
    const code = route.params.orderCode;
    const res = await axios.get(`/orders/${code}`);
    if (res.data.success) {
      const incoming = res.data.order;
      upiString.value = res.data.upi_string;
      loyaltyProgress.value = res.data.loyalty_progress || null;

      if (incoming.customer_name) {
        localStorage.setItem('qrmenu_customer_name', incoming.customer_name);
      }
      if (incoming.customer_phone) {
        localStorage.setItem('qrmenu_customer_phone', incoming.customer_phone);
      }
      if (incoming.table_number && incoming.restaurant_id) {
        localStorage.setItem(`qrmenu_active_table_${incoming.restaurant_id}`, incoming.table_number);
      }

      // Detect Live Food Status Updates
      if (prevOrderStatus.value && prevOrderStatus.value !== incoming.order_status) {
        playSound('status_update');

        if (incoming.order_status === 'preparing') {
          showToast({
            icon: 'fa-solid fa-fire-burner',
            title: 'Cooking in Progress! 👨‍🍳',
            message: 'Chef has started preparing your order in the kitchen.',
            color: 'bg-orange-500',
          });
          sendSystemNotification('Cooking Started! 👨‍🍳', 'Chef is now preparing your food in the kitchen.');
        } else if (incoming.order_status === 'ready') {
          showToast({
            icon: 'fa-solid fa-bell',
            title: 'Your Food is Ready! 🔔',
            message: 'Your dishes are prepared and on the way to your table!',
            color: 'bg-purple-600',
          });
          sendSystemNotification('Food Ready! 🔔', 'Your meal is freshly prepared and ready!');
        } else if (['completed', 'served'].includes(incoming.order_status)) {
          showToast({
            icon: 'fa-solid fa-utensils',
            title: 'Order Served! 🎉',
            message: 'Your order has been served. Enjoy your delicious food!',
            color: 'bg-emerald-600',
          });
          sendSystemNotification('Order Served! 🎉', 'Your order is served. Enjoy your meal!');
        }
      }

      // Detect Live Payment Status Updates
      if (prevPaymentStatus.value && prevPaymentStatus.value !== 'paid' && incoming.payment_status === 'paid') {
        playSound('payment_success');
        showToast({
          icon: 'fa-solid fa-circle-check',
          title: 'Payment Completed! 💰',
          message: `Payment of ${incoming.restaurant?.currency || '₹'}${incoming.total_amount} successfully received!`,
          color: 'bg-emerald-600',
        });
        sendSystemNotification('Payment Completed! 💰', `Payment of ${incoming.restaurant?.currency || '₹'}${incoming.total_amount} confirmed.`);
      }

      prevOrderStatus.value = incoming.order_status;
      prevPaymentStatus.value = incoming.payment_status;
      order.value = incoming;

      if (upiString.value && order.value.payment_status !== 'paid') {
        renderQr();
      }
      loadErrorCode.value = null;
    } else {
      loadErrorCode.value = 404;
    }
  } catch (err) {
    console.error('Failed to load order', err);
    loadErrorCode.value = err.response?.status || 500;
  } finally {
    loading.value = false;
  }
};

const renderQr = async () => {
  if (upiString.value) {
    try {
      upiQrDataUrl.value = await QRCode.toDataURL(upiString.value, {
        width: 240,
        margin: 1,
        color: {
          dark: '#0f172a',
          light: '#ffffff',
        },
        errorCorrectionLevel: 'M',
      });
    } catch (err) {
      console.error('QR code render error', err);
    }
  }
};

const confirmPayment = async () => {
  verifyingPayment.value = true;
  try {
    const code = route.params.orderCode;
    const res = await axios.post(`/orders/${code}/verify-payment`, {
      payment_status: 'paid',
      transaction_id: txnRef.value || ('UPI-' + Date.now().toString().slice(-8)),
    });

    if (res.data.success) {
      order.value = res.data.order;
    }
  } catch (err) {
    console.error('Failed to update payment', err);
  } finally {
    verifyingPayment.value = false;
  }
};

const isStepActive = (step) => {
  const status = order.value?.order_status;
  if (step === 1) return true;
  if (step === 2) return ['accepted', 'preparing', 'ready', 'served', 'completed'].includes(status);
  if (step === 3) return ['ready', 'served', 'completed'].includes(status);
  if (step === 4) return ['served', 'completed'].includes(status);
  return false;
};

const progressPercent = computed(() => {
  const status = order.value?.order_status;
  if (status === 'pending') return 25;
  if (status === 'accepted') return 40;
  if (status === 'preparing') return 55;
  if (status === 'ready') return 80;
  if (status === 'served' || status === 'completed') return 100;
  return 20;
});

const getStatusBadge = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-100 text-amber-800 border border-amber-300';
    case 'accepted': return 'bg-blue-100 text-blue-800 border border-blue-300';
    case 'preparing': return 'bg-orange-100 text-orange-800 border border-orange-300';
    case 'ready': return 'bg-purple-100 text-purple-800 border border-purple-300';
    case 'served':
    case 'completed': return 'bg-emerald-100 text-emerald-800 border border-emerald-300';
    case 'cancelled': return 'bg-rose-100 text-rose-800 border border-rose-300';
    default: return 'bg-slate-100 text-slate-800';
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  const timeStr = d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

  const diffMs = Date.now() - d.getTime();
  const diffMins = Math.floor(diffMs / 60000);

  if (diffMins < 1) {
    return `${timeStr} (Just now)`;
  } else if (diffMins < 60) {
    return `${timeStr} (${diffMins}m ago)`;
  } else {
    const diffHours = Math.floor(diffMins / 60);
    if (diffHours < 24) {
      return `${timeStr} (${diffHours}h ago)`;
    }
    return `${d.toLocaleDateString([], { month: 'short', day: 'numeric' })} at ${timeStr}`;
  }
};

let pollTimer = null;

onMounted(() => {
  fetchOrder();
  // Automatically poll every 5s so customer gets live kitchen & food updates
  pollTimer = setInterval(() => {
    if (order.value && !['completed', 'served', 'cancelled'].includes(order.value.order_status)) {
      fetchOrder();
    }
  }, 5000);
});

onUnmounted(() => {
  if (pollTimer) clearInterval(pollTimer);
  clearAllGameTimers();
});
</script>
