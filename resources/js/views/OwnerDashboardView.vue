<template>
  <div class="min-h-screen bg-slate-900 text-slate-100 flex flex-col">
    <!-- Live Notification Banner (New Order & Payment Completed) -->
    <transition enter-active-class="transform transition ease-out duration-300" enter-from-class="translate-y-[-100%] opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div
        v-if="newOrderToast"
        :class="[
          'fixed top-4 right-4 z-50 text-slate-950 px-4 py-3 rounded-2xl shadow-2xl border-2 border-white/80 flex items-center gap-3',
          newOrderToast.type === 'payment' ? 'bg-gradient-to-r from-emerald-400 to-teal-400' : 'bg-gradient-to-r from-amber-500 to-orange-500'
        ]"
      >
        <div class="w-10 h-10 rounded-xl bg-slate-950 text-white flex items-center justify-center font-extrabold text-lg shadow">
          <i :class="newOrderToast.type === 'payment' ? 'fa-solid fa-circle-check text-emerald-400' : 'fa-solid fa-bell animate-bounce text-amber-400'"></i>
        </div>
        <div>
          <div class="font-black text-xs uppercase tracking-wider flex items-center gap-1.5">
            <span>{{ newOrderToast.title || 'Notification' }}</span>
            <span class="bg-slate-950 text-white text-[9px] px-1.5 py-0.2 rounded font-mono">Live</span>
          </div>
          <div class="text-xs font-bold mt-0.5">
            #{{ newOrderToast.code }} • {{ newOrderToast.location }} • {{ restaurant?.currency }}{{ newOrderToast.amount }}
          </div>
          <div class="text-[10px] text-slate-900 font-semibold truncate max-w-[200px]">
            {{ newOrderToast.customer }}
          </div>
        </div>
        <button @click="newOrderToast = null" class="w-6 h-6 rounded-full bg-slate-950/20 hover:bg-slate-950/40 text-slate-950 flex items-center justify-center transition cursor-pointer ml-1">
          <i class="fa-solid fa-xmark text-xs"></i>
        </button>
      </div>
    </transition>
    <!-- Top Header / Bar -->
    <header class="bg-slate-950/80 border-b border-slate-800 sticky top-0 z-40 backdrop-blur-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <router-link to="/" class="flex items-center space-x-2">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
              <i class="fa-solid fa-qrcode text-lg text-white"></i>
            </div>
            <span class="font-extrabold text-white text-lg tracking-tight hidden sm:inline">QRMenu</span>
          </router-link>

          <span class="text-slate-600 hidden sm:inline">/</span>

          <!-- Active Restaurant Badge -->
          <div v-if="restaurant" class="flex items-center space-x-2 bg-slate-800/80 border border-slate-700 px-3 py-1.5 rounded-xl">
            <img
              :src="restaurant.logo_url || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100&fit=crop'"
              class="w-6 h-6 rounded-lg object-cover"
            />
            <span class="text-xs font-bold text-white">{{ restaurant.name }}</span>
            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          </div>
        </div>

        <div class="flex items-center space-x-2 sm:space-x-3">
          <!-- View Customer Menu Button -->
          <a
            v-if="restaurant"
            :href="`/r/${restaurant.slug}`"
            target="_blank"
            class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
            title="Open Public QR Menu"
          >
            <i class="fa-solid fa-arrow-up-right-from-square"></i>
            <span class="hidden sm:inline">Open Live Menu</span>
          </a>

          <!-- Live Alerts & Sound Toggle Button -->
          <button
            @click="toggleNotificationAlerts"
            class="px-3 py-1.5 rounded-xl border text-xs font-bold transition flex items-center gap-1.5 cursor-pointer"
            :class="notificationsEnabled ? 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40' : 'bg-slate-800 text-slate-300 border-slate-700 hover:text-white'"
            :title="notificationsEnabled ? 'Live Order & Payment Alerts Active' : 'Click to enable Live Sound & Push Alerts'"
          >
            <i :class="notificationsEnabled ? 'fa-solid fa-bell text-emerald-400' : 'fa-regular fa-bell text-slate-400'"></i>
            <span class="hidden sm:inline">{{ notificationsEnabled ? 'Alerts ON' : 'Enable Alerts' }}</span>
          </button>

          <!-- Logout Button -->
          <button
            @click="handleLogout"
            class="p-2 sm:px-3 sm:py-1.5 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-xl text-xs font-semibold transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-power-off"></i>
            <span class="hidden sm:inline">Logout</span>
          </button>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-1 sm:space-x-2 overflow-x-auto no-scrollbar border-t border-slate-800/60 py-1">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          :class="[
            'px-3.5 py-2 text-xs font-bold rounded-xl whitespace-nowrap transition flex items-center gap-2',
            activeTab === tab.id
              ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
              : 'text-slate-400 hover:text-white hover:bg-slate-800/60'
          ]"
        >
          <i :class="tab.icon"></i>
          <span>{{ tab.name }}</span>
          <span
            v-if="tab.badge && tab.badge > 0"
            class="ml-1 px-1.5 py-0.2 rounded-full text-[10px] bg-rose-500 text-white font-extrabold"
          >
            {{ tab.badge }}
          </span>
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-amber-500 mb-3"></i>
        <p class="text-slate-400 text-sm">Loading restaurant dashboard...</p>
      </div>

      <div v-else>
        <!-- TAB 1: OVERVIEW & STATS -->
        <div v-if="activeTab === 'overview'" class="space-y-8">
          <!-- Metric Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-lg">
              <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Today's Sales</span>
                <i class="fa-solid fa-wallet text-amber-400 text-base"></i>
              </div>
              <div class="text-2xl font-extrabold text-white">
                {{ restaurant.currency }}{{ stats.today_sales ? stats.today_sales.toFixed(2) : '0.00' }}
              </div>
              <div class="text-[11px] text-slate-400 mt-1">Paid orders today</div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-lg">
              <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Pending Orders</span>
                <i class="fa-solid fa-fire-burner text-orange-400 text-base"></i>
              </div>
              <div class="text-2xl font-extrabold text-orange-400">
                {{ stats.pending_orders_count || 0 }}
              </div>
              <div class="text-[11px] text-slate-400 mt-1">Needs kitchen action</div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-lg">
              <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Total Lifetime Revenue</span>
                <i class="fa-solid fa-chart-line text-emerald-400 text-base"></i>
              </div>
              <div class="text-2xl font-extrabold text-emerald-400">
                {{ restaurant.currency }}{{ stats.total_sales ? stats.total_sales.toFixed(2) : '0.00' }}
              </div>
              <div class="text-[11px] text-slate-400 mt-1">Completed orders</div>
            </div>

            <div class="bg-slate-800/80 border border-slate-700/80 rounded-2xl p-5 shadow-lg">
              <div class="flex items-center justify-between text-slate-400 text-xs mb-2">
                <span>Active Dishes in Menu</span>
                <i class="fa-solid fa-utensils text-blue-400 text-base"></i>
              </div>
              <div class="text-2xl font-extrabold text-blue-400">
                {{ menuItems.length }}
              </div>
              <div class="text-[11px] text-slate-400 mt-1">Across {{ categories.length }} categories</div>
            </div>
          </div>

          <!-- Quick Action Banner -->
          <div class="bg-gradient-to-r from-amber-500/10 via-orange-500/10 to-transparent border border-amber-500/30 rounded-3xl p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-xs font-semibold mb-3">
                <i class="fa-solid fa-qrcode"></i> Your Restaurant QR System is Ready
              </div>
              <h3 class="text-xl sm:text-2xl font-extrabold text-white">Generate & Print Table QR Codes</h3>
              <p class="text-xs sm:text-sm text-slate-300 mt-1 max-w-xl">
                Print QR codes for each table so customers can scan, order, and pay directly to your UPI ID (<span class="font-mono text-amber-400">{{ restaurant.upi_id || 'Not configured' }}</span>).
              </p>
            </div>
            <div class="flex items-center gap-3 shrink-0 flex-wrap">
              <button
                @click="openPdfModal"
                class="px-4 py-3 bg-slate-800 hover:bg-slate-700 text-rose-300 hover:text-white border border-rose-500/30 font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer"
                title="Export complete restaurant menu card as PDF"
              >
                <i class="fa-solid fa-file-pdf text-rose-400"></i> Download Menu PDF
              </button>
              <button
                @click="activeTab = 'qrcodes'"
                class="px-5 py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow-lg transition flex items-center gap-2 cursor-pointer"
              >
                <i class="fa-solid fa-print"></i> Generate Table QRs
              </button>
            </div>
          </div>

          <!-- Recent Orders Preview -->
          <div class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-base font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-amber-400"></i> Latest Orders
              </h3>
              <button @click="activeTab = 'orders'" class="text-xs text-amber-400 hover:underline font-semibold">
                View All Orders →
              </button>
            </div>

            <div v-if="orders.length === 0" class="text-center py-8 text-slate-500 text-xs">
              No orders received yet. Orders placed by customers will appear here in real-time.
            </div>

            <div v-else class="divide-y divide-slate-700/60">
              <div
                v-for="order in orders.slice(0, 5)"
                :key="order.id"
                class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs"
              >
                <div>
                  <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-bold text-white">#{{ order.order_code }}</span>
                    <span class="bg-slate-700 px-2 py-0.5 rounded text-[10px] text-amber-300 font-semibold">
                      {{ order.table_number }}
                    </span>
                    <span class="text-slate-400">{{ order.customer_name }} ({{ order.customer_phone }})</span>
                    <span class="text-amber-400 text-[10px] font-medium flex items-center gap-1 bg-slate-800 px-2 py-0.5 rounded border border-slate-700">
                      <i class="fa-regular fa-clock text-[9px]"></i> {{ formatOrderTime(order.created_at) }}
                    </span>
                  </div>
                  <div class="text-slate-400 text-[11px] mt-0.5">
                    {{ order.items ? order.items.map(i => `${i.item_name} x${i.quantity}`).join(', ') : '' }}
                  </div>
                </div>

                <div class="flex items-center gap-3">
                  <div class="text-right">
                    <div class="font-extrabold text-white">{{ restaurant.currency }}{{ order.total_amount }}</div>
                    <span
                      :class="[
                        'text-[10px] font-bold px-2 py-0.5 rounded-full capitalize',
                        order.payment_status === 'paid' ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400'
                      ]"
                    >
                      {{ order.payment_status }}
                    </span>
                  </div>

                  <span
                    :class="[
                      'px-2.5 py-1 rounded-lg font-bold capitalize text-[11px]',
                      getOrderBadge(order.order_status)
                    ]"
                  >
                    {{ order.order_status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: LIVE ORDERS MANAGEMENT (KITCHEN & POS) -->
        <div v-if="activeTab === 'orders'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-white">Live Orders Management</h2>
              <p class="text-xs text-slate-400">Accept orders, send to kitchen, and track payments</p>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="fetchOrders"
                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-white rounded-xl text-xs font-bold border border-slate-700 flex items-center gap-1.5 transition"
              >
                <i class="fa-solid fa-arrows-rotate"></i> Refresh
              </button>

              <select
                v-model="orderStatusFilter"
                @change="fetchOrders"
                class="px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              >
                <option value="all">All Statuses</option>
                <option value="pending">Pending</option>
                <option value="accepted">Accepted</option>
                <option value="preparing">Preparing</option>
                <option value="ready">Ready</option>
                <option value="served">Served / Completed</option>
              </select>
            </div>
          </div>

          <div v-if="orders.length === 0" class="bg-slate-800/60 rounded-3xl p-12 text-center border border-slate-700">
            <i class="fa-solid fa-receipt text-4xl text-slate-600 mb-2"></i>
            <p class="text-slate-400 text-sm font-medium">No orders found.</p>
          </div>

          <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="order in orders"
              :key="order.id"
              class="bg-slate-800/90 rounded-2xl p-5 border border-slate-700 shadow-xl flex flex-col justify-between"
            >
              <div>
                <!-- Top Card Info -->
                <div class="flex items-start justify-between border-b border-slate-700/80 pb-3 mb-3">
                  <div>
                    <div class="flex items-center gap-2 flex-wrap">
                      <span class="font-extrabold text-white text-sm">#{{ order.order_code }}</span>

                      <!-- Salon Token Badge -->
                      <span
                        v-if="order.token_number || restaurant?.business_type === 'salon'"
                        class="bg-cyan-500/20 text-cyan-300 font-black px-2.5 py-0.5 rounded text-[10px] border border-cyan-500/40 flex items-center gap-1"
                      >
                        <i class="fa-solid fa-ticket text-[9px]"></i> Token #{{ order.token_number || 1 }}
                      </span>

                      <!-- Slot pill if booking_time -->
                      <span
                        v-if="order.booking_time"
                        class="bg-cyan-900/60 text-cyan-200 font-semibold px-2 py-0.5 rounded text-[10px] border border-cyan-700/50 flex items-center gap-1"
                      >
                        <i class="fa-solid fa-clock text-[9px]"></i> {{ order.booking_time }} ({{ order.booking_date || 'Today' }})
                      </span>

                      <span
                        v-if="order.order_type === 'delivery'"
                        class="bg-blue-500/20 text-blue-300 font-bold px-2 py-0.5 rounded text-[10px] border border-blue-500/30 flex items-center gap-1"
                      >
                        <i class="fa-solid fa-motorcycle text-[9px]"></i> Home Delivery
                      </span>
                      <span
                        v-else-if="order.order_type === 'takeaway'"
                        class="bg-purple-500/20 text-purple-300 font-bold px-2 py-0.5 rounded text-[10px] border border-purple-500/30 flex items-center gap-1"
                      >
                        <i class="fa-solid fa-bag-shopping text-[9px]"></i> Takeaway
                      </span>
                      <span
                        v-else
                        class="bg-amber-500/20 text-amber-300 font-bold px-2 py-0.5 rounded text-[10px] border border-amber-500/30 flex items-center gap-1"
                      >
                        <i :class="restaurant?.business_type === 'salon' ? 'fa-solid fa-chair text-cyan-400 text-[9px]' : 'fa-solid fa-chair text-[9px]'"></i>
                        {{ order.chair_or_specialist || order.table_number }}
                      </span>
                    </div>
                    <div class="text-[11px] text-slate-300 mt-1.5 flex flex-wrap items-center gap-x-2 gap-y-1">
                      <span><i class="fa-solid fa-user text-slate-500 mr-1"></i> {{ order.customer_name }} • {{ order.customer_phone }}</span>
                      <span class="text-slate-600 hidden sm:inline">•</span>
                      <span class="text-amber-400 font-semibold flex items-center gap-1 bg-amber-500/10 px-2 py-0.5 rounded-md border border-amber-500/20 text-[10px]">
                        <i class="fa-regular fa-clock text-[10px]"></i> {{ formatOrderTime(order.created_at) }}
                      </span>
                    </div>
                  </div>

                  <span
                    :class="[
                      'px-2.5 py-1 rounded-xl text-[10px] font-extrabold capitalize border',
                      getOrderBadge(order.order_status)
                    ]"
                  >
                    {{ order.order_status }}
                  </span>
                </div>

                <!-- Items Breakdown -->
                <div class="space-y-1.5 my-3 text-xs">
                  <div
                    v-for="item in order.items"
                    :key="item.id"
                    class="flex items-center justify-between text-slate-300"
                  >
                    <span>{{ item.quantity }}x {{ item.item_name }}</span>
                    <span class="font-mono text-slate-400">{{ restaurant.currency }}{{ item.subtotal }}</span>
                  </div>
                </div>

                <!-- Delivery Address if Home Delivery -->
                <div v-if="order.delivery_address" class="p-2 bg-blue-500/10 border border-blue-500/20 rounded-xl text-[11px] text-blue-300 mb-3">
                  <i class="fa-solid fa-location-dot mr-1 text-blue-400"></i>
                  <span class="font-bold">Delivery Address:</span> {{ order.delivery_address }}
                </div>

                <!-- Special Notes if any -->
                <div v-if="order.special_notes" class="p-2 bg-amber-500/10 border border-amber-500/20 rounded-xl text-[11px] text-amber-300 mb-3">
                  <i class="fa-solid fa-note-sticky mr-1"></i> {{ order.special_notes }}
                </div>

                <!-- Payment status row -->
                <div class="bg-slate-900/80 p-2.5 rounded-xl border border-slate-700/60 flex items-center justify-between text-xs mb-4">
                  <div>
                    <span class="text-slate-400 block text-[10px]">Payment ({{ order.payment_method }}):</span>
                    <span :class="order.payment_status === 'paid' ? 'text-emerald-400 font-bold' : 'text-amber-400 font-bold'">
                      {{ order.payment_status === 'paid' ? '✓ Paid' : 'Pending' }}
                    </span>
                  </div>
                  <div class="text-right">
                    <span class="text-slate-400 block text-[10px]">Total Amount:</span>
                    <span class="text-white font-extrabold text-sm">{{ restaurant.currency }}{{ order.total_amount }}</span>
                    <span v-if="order.discount_amount > 0" class="block text-[10px] text-emerald-400 font-bold">
                      🎮 5% Game Win (-{{ restaurant.currency }}{{ order.discount_amount }})
                    </span>
                  </div>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="space-y-2 pt-2 border-t border-slate-700/60">
                <div class="grid grid-cols-2 gap-2">
                  <button
                    v-if="order.order_status === 'pending'"
                    @click="updateOrderStatus(order.id, 'accepted')"
                    :disabled="updatingOrderId === order.id"
                    class="py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50"
                  >
                    <i v-if="updatingOrderId === order.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    <i v-else class="fa-solid fa-check"></i>
                    <span>{{ restaurant?.business_type === 'salon' ? 'Accept Booking' : 'Accept Order' }}</span>
                  </button>

                  <button
                    v-if="['pending', 'accepted'].includes(order.order_status)"
                    @click="updateOrderStatus(order.id, 'preparing')"
                    :disabled="updatingOrderId === order.id"
                    class="py-2 bg-orange-600 hover:bg-orange-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50"
                  >
                    <i v-if="updatingOrderId === order.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    <i v-else :class="restaurant?.business_type === 'salon' ? 'fa-solid fa-scissors' : 'fa-solid fa-fire-burner'"></i>
                    <span>{{ restaurant?.business_type === 'salon' ? 'In Chair (Styling)' : 'In Kitchen' }}</span>
                  </button>

                  <button
                    v-if="order.order_status === 'preparing'"
                    @click="updateOrderStatus(order.id, 'ready')"
                    :disabled="updatingOrderId === order.id"
                    class="py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50"
                  >
                    <i v-if="updatingOrderId === order.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    <i v-else class="fa-solid fa-bell"></i>
                    <span>{{ restaurant?.business_type === 'salon' ? 'Styling Finished' : 'Food Ready' }}</span>
                  </button>

                  <button
                    v-if="['ready', 'preparing'].includes(order.order_status)"
                    @click="updateOrderStatus(order.id, 'completed')"
                    :disabled="updatingOrderId === order.id"
                    class="py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50"
                  >
                    <i v-if="updatingOrderId === order.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    <i v-else class="fa-solid fa-check-double"></i>
                    <span>Served</span>
                  </button>

                  <button
                    v-if="order.payment_status !== 'paid'"
                    @click="markOrderPaid(order.id)"
                    :disabled="updatingOrderId === order.id"
                    class="py-2 bg-slate-700 hover:bg-slate-600 text-amber-300 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1 cursor-pointer disabled:opacity-50"
                  >
                    <i v-if="updatingOrderId === order.id" class="fa-solid fa-circle-notch fa-spin text-xs"></i>
                    <i v-else class="fa-solid fa-money-bill-check"></i>
                    <span>Mark Paid</span>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 3: MENU ITEMS / DISHES / SERVICES -->
        <div v-if="activeTab === 'menu'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-white">
                {{ restaurant?.business_type === 'salon' ? 'Services & Rate Card' : (restaurant?.business_type === 'retail' ? 'Products & Catalog' : 'Menu Dishes & Items') }}
              </h2>
              <p class="text-xs text-slate-400">
                {{ restaurant?.business_type === 'salon' ? 'Add salon services, set duration & pricing, and manage availability' : (restaurant?.business_type === 'retail' ? 'Add products, set prices, and manage stock' : 'Add dishes, set prices, and toggle available/sold-out') }}
              </p>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
              <button
                @click="openPdfModal"
                class="px-4 py-2.5 bg-slate-800 hover:bg-slate-700 text-rose-300 hover:text-white border border-rose-500/30 rounded-xl text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow"
                :title="restaurant?.business_type === 'salon' ? 'Download or Print complete Salon Rate Card as PDF' : 'Download or Print complete Restaurant Menu as PDF'"
              >
                <i class="fa-solid fa-file-pdf text-rose-400"></i> {{ restaurant?.business_type === 'salon' ? 'Download Rate Card PDF' : 'Download Menu PDF' }}
              </button>

              <button
                @click="openDishModal(null)"
                class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition flex items-center gap-2 cursor-pointer"
              >
                <i class="fa-solid fa-plus"></i> {{ restaurant?.business_type === 'salon' ? 'Add New Service' : (restaurant?.business_type === 'retail' ? 'Add New Product' : 'Add New Dish') }}
              </button>
            </div>
          </div>

          <!-- Dishes / Services Table/Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="item in menuItems"
              :key="item.id"
              class="bg-slate-800/90 rounded-2xl p-4 border border-slate-700 shadow-md flex gap-3 hover:border-slate-600 transition"
            >
              <div class="w-20 h-20 rounded-xl overflow-hidden bg-slate-900 shrink-0">
                <img
                  :src="item.image_url || 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=300&fit=crop'"
                  class="w-full h-full object-cover"
                />
              </div>

              <div class="flex-1 flex flex-col justify-between">
                <div>
                  <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-white line-clamp-1">{{ item.name }}</span>
                    <span class="font-extrabold text-amber-400 text-xs">{{ restaurant.currency }}{{ item.price }}</span>
                  </div>
                  <span class="text-[10px] text-slate-400 block mt-0.5">
                    {{ item.category?.name }}
                    <template v-if="restaurant?.business_type === 'salon'">
                      • <span class="text-amber-400 font-bold">⏱️ {{ item.duration_minutes || 30 }}m</span>
                      <span v-if="item.target_audience && item.target_audience !== 'all'" class="text-slate-400 capitalize"> • {{ item.target_audience }}</span>
                    </template>
                    <template v-else-if="restaurant?.business_type === 'retail'">
                      <span v-if="item.target_audience" class="text-slate-400 capitalize"> • {{ item.target_audience }}</span>
                    </template>
                    <template v-else-if="item.food_type">
                      • <span :class="item.food_type === 'veg' ? 'text-emerald-400' : 'text-rose-400'" class="font-bold uppercase">
                        {{ item.food_type }}
                      </span>
                    </template>
                  </span>
                </div>

                <div class="flex items-center justify-between pt-2 border-t border-slate-700/60 mt-2">
                  <!-- Stock Toggle Switch -->
                  <button
                    @click="toggleAvailability(item.id)"
                    :class="[
                      'text-[10px] font-bold px-2 py-1 rounded-lg transition border',
                      item.is_available
                        ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30'
                        : 'bg-rose-500/20 text-rose-400 border-rose-500/30'
                    ]"
                  >
                    {{ item.is_available ? 'Available' : 'Unavailable' }}
                  </button>

                  <div class="flex items-center space-x-1">
                    <button
                      @click="openDishModal(item)"
                      class="p-1.5 text-slate-400 hover:text-amber-400 rounded-lg hover:bg-slate-700 transition"
                      title="Edit"
                    >
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button
                      @click="deleteMenuItem(item.id)"
                      class="p-1.5 text-slate-400 hover:text-rose-400 rounded-lg hover:bg-slate-700 transition"
                      title="Delete"
                    >
                      <i class="fa-solid fa-trash-can"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 4: CATEGORIES -->
        <div v-if="activeTab === 'categories'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-white">
                {{ restaurant?.business_type === 'salon' ? 'Service Categories' : (restaurant?.business_type === 'retail' ? 'Product Categories' : 'Menu Categories') }}
              </h2>
              <p class="text-xs text-slate-400">
                {{ restaurant?.business_type === 'salon' ? 'Organize salon services (Hair Cuts, Shave & Beard, Facial & Spa)' : (restaurant?.business_type === 'retail' ? 'Organize products (Shirts, Jeans, Accessories)' : 'Organize your menu into sections (Starters, Breads, Drinks)') }}
              </p>
            </div>

            <button
              @click="openCategoryModal(null)"
              class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition flex items-center gap-2"
            >
              <i class="fa-solid fa-plus"></i> Add Category
            </button>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
              v-for="cat in categories"
              :key="cat.id"
              class="bg-slate-800/90 rounded-2xl p-5 border border-slate-700 shadow-md flex items-center justify-between"
            >
              <div>
                <h3 class="font-bold text-white text-sm">{{ cat.name }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ cat.description || 'No description' }}</p>
                <span class="text-[10px] text-amber-400 mt-1 block font-semibold">
                  {{ cat.menu_items_count || 0 }} {{ restaurant?.business_type === 'salon' ? 'Services' : (restaurant?.business_type === 'retail' ? 'Products' : 'Dishes') }}
                </span>
              </div>

              <div class="flex items-center space-x-1">
                <button
                  @click="openCategoryModal(cat)"
                  class="p-2 text-slate-400 hover:text-amber-400 rounded-xl hover:bg-slate-700 transition"
                >
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button
                  @click="deleteCategory(cat.id)"
                  class="p-2 text-slate-400 hover:text-rose-400 rounded-xl hover:bg-slate-700 transition"
                >
                  <i class="fa-solid fa-trash-can"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB: FLOOR & DYNAMIC TABLES MANAGEMENT -->
        <div v-if="activeTab === 'tables'" class="space-y-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <h2 class="text-xl font-bold text-white flex items-center gap-2">
                <i class="fa-solid fa-chair text-amber-500"></i> Floor & Tables Management
              </h2>
              <p class="text-xs text-slate-400">Add tables or chairs, configure seating capacity, and monitor live occupancy in real-time</p>
            </div>

            <div class="flex items-center gap-2">
              <button
                @click="showBulkTableModal = true"
                class="px-3.5 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold rounded-xl text-xs border border-slate-700 flex items-center gap-1.5 transition"
              >
                <i class="fa-solid fa-layer-group text-amber-400"></i> Bulk Generate
              </button>

              <button
                @click="showAddTableModal = true"
                class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition flex items-center gap-1.5"
              >
                <i class="fa-solid fa-plus"></i> Add Table
              </button>
            </div>
          </div>

          <!-- Table Occupancy Stats -->
          <div class="grid grid-cols-3 gap-3">
            <div class="bg-slate-800/80 p-4 rounded-2xl border border-slate-700">
              <span class="text-slate-400 text-xs block">Total Tables</span>
              <span class="text-xl font-extrabold text-white">{{ tableStats.total || tables.length }}</span>
            </div>

            <div class="bg-emerald-500/10 p-4 rounded-2xl border border-emerald-500/30">
              <span class="text-emerald-400 text-xs block">Available (Free)</span>
              <span class="text-xl font-extrabold text-emerald-400">{{ tableStats.available || 0 }}</span>
            </div>

            <div class="bg-rose-500/10 p-4 rounded-2xl border border-rose-500/30">
              <span class="text-rose-400 text-xs block">Occupied (Diners Active)</span>
              <span class="text-xl font-extrabold text-rose-400">{{ tableStats.occupied || 0 }}</span>
            </div>
          </div>

          <!-- Tables Grid -->
          <div v-if="tables.length === 0" class="bg-slate-800/60 rounded-3xl p-12 text-center border border-slate-700">
            <i class="fa-solid fa-chair text-4xl text-slate-600 mb-2"></i>
            <p class="text-slate-400 text-sm font-medium">No tables configured yet.</p>
            <button
              @click="showBulkTableModal = true"
              class="mt-3 px-4 py-2 bg-amber-500 text-slate-950 text-xs font-bold rounded-xl"
            >
              Generate Tables
            </button>
          </div>

          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            <div
              v-for="tbl in tables"
              :key="tbl.id"
              :class="[
                'p-4 rounded-2xl border transition flex flex-col justify-between shadow-lg',
                tbl.status === 'occupied'
                  ? 'bg-slate-800/95 border-rose-500/60 ring-1 ring-rose-500/30'
                  : 'bg-slate-800/80 border-slate-700/80 hover:border-slate-600'
              ]"
            >
              <div>
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <span class="font-extrabold text-white text-sm">{{ tbl.name }}</span>
                    <span class="text-[10px] text-slate-400 bg-slate-900 px-2 py-0.5 rounded font-mono">
                      {{ tbl.code || 'T' }}
                    </span>
                  </div>

                  <span
                    :class="[
                      'text-[10px] font-bold px-2 py-0.5 rounded-full capitalize flex items-center gap-1',
                      tbl.status === 'occupied' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30'
                    ]"
                  >
                    <i class="fa-solid fa-circle text-[6px]"></i>
                    {{ tbl.status === 'occupied' ? 'Occupied' : 'Free' }}
                  </span>
                </div>

                <div class="text-xs text-slate-400 mb-3 flex items-center gap-1.5">
                  <i class="fa-solid fa-users text-[10px] text-amber-400"></i>
                  <span>{{ tbl.seating_capacity }} Seater Table</span>
                </div>

                <!-- Active Diner Information if Occupied -->
                <div v-if="tbl.status === 'occupied'" class="bg-rose-950/40 p-2.5 rounded-xl border border-rose-900/60 text-xs space-y-2 mb-3">
                  <div class="flex items-center justify-between text-rose-300 font-extrabold border-b border-rose-900/40 pb-1">
                    <span>{{ tbl.orders_count || 1 }} Active {{ (tbl.orders_count || 1) > 1 ? 'Orders' : 'Order' }}</span>
                    <span>Total: {{ restaurant.currency }}{{ (tbl.table_total_amount || 0).toFixed(2) }}</span>
                  </div>

                  <div class="space-y-1 max-h-28 overflow-y-auto pr-1">
                    <div
                      v-for="o in (tbl.active_orders || [tbl.active_order].filter(Boolean))"
                      :key="o.id"
                      class="flex items-center justify-between text-[11px] text-slate-300 bg-slate-900/70 p-1.5 rounded-lg border border-slate-800"
                    >
                      <div class="truncate mr-2">
                        <span class="font-extrabold text-white">#{{ o.order_code }}</span>
                        <div class="text-slate-400 text-[10px] truncate flex items-center gap-1">
                          <span>{{ o.customer_name }}</span>
                          <span>•</span>
                          <span class="text-amber-400 font-medium">{{ formatOrderTime(o.created_at) }}</span>
                        </div>
                      </div>
                      <span class="font-mono text-amber-300 font-bold shrink-0">{{ restaurant.currency }}{{ o.total_amount }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Table Bottom Actions -->
              <div class="pt-3 border-t border-slate-700/60 flex items-center justify-between gap-2">
                <button
                  v-if="tbl.status === 'occupied'"
                  @click="vacateTable(tbl.id)"
                  class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl transition flex items-center justify-center gap-1"
                  title="Guest has left - mark table as Free"
                >
                  <i class="fa-solid fa-bell-concierge"></i> Vacate / Free
                </button>

                <div v-else class="text-[11px] text-emerald-400 font-semibold flex items-center gap-1">
                  <i class="fa-solid fa-check text-xs"></i> Ready for Diners
                </div>

                <button
                  @click="deleteTable(tbl.id)"
                  class="p-2 text-slate-400 hover:text-rose-400 rounded-xl hover:bg-slate-700 transition"
                  title="Delete Table"
                >
                  <i class="fa-solid fa-trash-can text-xs"></i>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 5: TABLE QR CODES GENERATOR -->
        <div v-if="activeTab === 'qrcodes'" class="space-y-6">
          <div>
            <h2 class="text-xl font-bold text-white">Table QR Codes & Print Ready Generator</h2>
            <p class="text-xs text-slate-400">Generate, customize, download, and print high-resolution QR codes for each table</p>
          </div>

          <!-- Table Selector Control -->
          <div class="bg-slate-800/80 p-5 rounded-2xl border border-slate-700 flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
              <label class="text-xs font-semibold text-slate-300">Select Table to Preview:</label>
              <select
                v-model="selectedTableNumber"
                class="px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              >
                <option value="main">Main Menu (General QR for Takeaway / Social Media)</option>
                <option v-for="tbl in tables" :key="tbl.id" :value="tbl.name">
                  {{ tbl.name }} ({{ tbl.seating_capacity }} Seater) - {{ tbl.status === 'occupied' ? 'Occupied' : 'Free' }}
                </option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <a
                v-if="qrDataUrl"
                :href="qrDataUrl"
                :download="`${restaurant.slug}-${selectedTableNumber === 'main' ? 'general' : 'table-' + selectedTableNumber}-qr.png`"
                class="px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-2"
                title="Download QR as Image"
              >
                <i class="fa-solid fa-download"></i> Download PNG
              </a>
              <button
                @click="printQrCode"
                class="px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow transition flex items-center gap-2"
              >
                <i class="fa-solid fa-print"></i> Print Standee Card
              </button>
            </div>
          </div>

          <!-- Printable Table Standee Card Preview -->
          <div class="flex justify-center py-6">
            <div id="printable-qr" class="bg-white text-slate-900 w-80 p-6 rounded-3xl shadow-2xl text-center border-4 border-amber-500">
              <div class="flex items-center justify-center gap-2 mb-2">
                <div class="w-8 h-8 rounded-xl bg-amber-500 flex items-center justify-center text-slate-950 font-bold">
                  <i class="fa-solid fa-utensils"></i>
                </div>
                <h3 class="font-extrabold text-lg text-slate-900 leading-tight">{{ restaurant.name }}</h3>
              </div>

              <div class="bg-amber-100 text-amber-900 font-extrabold text-xs px-3 py-1 rounded-full inline-block my-2">
                {{ selectedTableNumber === 'main' ? 'MAIN MENU / TAKEAWAY' : 'TABLE ' + selectedTableNumber }}
              </div>

              <p class="text-[11px] text-slate-500 mb-3">Scan with Camera or UPI App to View Menu & Order</p>

              <!-- QR Image -->
              <div class="p-3 bg-white border border-slate-200 rounded-2xl inline-block shadow-sm mb-3">
                <img
                  v-if="qrDataUrl"
                  :src="qrDataUrl"
                  :alt="`QR Code`"
                  class="w-52 h-52 mx-auto rounded-lg object-contain block"
                />
                <div v-else class="w-52 h-52 flex flex-col items-center justify-center text-slate-400 text-xs">
                  <i class="fa-solid fa-circle-notch fa-spin text-2xl text-amber-500 mb-2"></i>
                  <span>Generating QR...</span>
                </div>
              </div>

              <div class="text-[11px] font-bold text-slate-800">
                {{ restaurant.enable_online_payment ? 'Direct Pay via UPI • Fast Service' : 'Scan to View Menu & Order • Pay at Counter' }}
              </div>
              <div class="text-[9px] text-slate-400 mt-1 font-mono truncate">{{ currentTableUrl }}</div>
            </div>
          </div>
        </div>

        <!-- TAB 6: RESTAURANT & PAYMENT SETTINGS -->
        <div v-if="activeTab === 'settings'" class="max-w-3xl space-y-6">
          <div>
            <h2 class="text-xl font-bold text-white">Restaurant Profile & Payment Configuration</h2>
            <p class="text-xs text-slate-400">Update your payment UPI ID, tax percentage, table count, and business profile details</p>
          </div>

          <form @submit.prevent="updateRestaurantSettings" class="bg-slate-800/80 p-6 rounded-3xl border border-slate-700 space-y-5">
            <div v-if="settingsSuccessMsg" class="p-3 bg-emerald-500/20 border border-emerald-500/50 rounded-xl text-emerald-300 text-xs">
              <i class="fa-solid fa-check-circle mr-1"></i> {{ settingsSuccessMsg }}
            </div>

            <!-- General Details -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Business Name *</label>
                <input
                  v-model="settingsForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Tagline / Subheading</label>
                <input
                  v-model="settingsForm.tagline"
                  type="text"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                />
              </div>
            </div>

            <!-- Business Category & Slot Duration -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Business Category</label>
                <select
                  v-model="settingsForm.business_type"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                >
                  <option value="food">🍔 Food & Dining (Restaurant, Cafe, Dhaba)</option>
                  <option value="salon">💈 Barber & Salon (Haircuts, Grooming, Spa)</option>
                  <option value="retail">👗 Retail & Clothing (Boutique, Store)</option>
                </select>
              </div>

              <div v-if="settingsForm.business_type === 'salon'">
                <label class="block text-xs font-semibold text-slate-300 mb-1">Appointment Slot Duration</label>
                <select
                  v-model.number="settingsForm.slot_duration_minutes"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                >
                  <option :value="15">15 Minutes</option>
                  <option :value="20">20 Minutes</option>
                  <option :value="30">30 Minutes (Standard)</option>
                  <option :value="45">45 Minutes</option>
                  <option :value="60">60 Minutes</option>
                </select>
              </div>
            </div>

            <!-- Salon Working Hours -->
            <div v-if="settingsForm.business_type === 'salon'" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Daily Opening Time</label>
                <input
                  v-model="settingsForm.opening_time"
                  type="time"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Daily Closing Time</label>
                <input
                  v-model="settingsForm.closing_time"
                  type="time"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                />
              </div>
            </div>

            <!-- Payment Setup Section -->
            <div class="pt-4 border-t border-slate-700/80">
              <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                  <i class="fa-solid fa-indian-rupee-sign"></i> Payment & Billing Modes
                </h3>
              </div>

              <!-- Payment Toggles -->
              <div class="space-y-3 mb-4">
                <!-- Toggle 1: Online UPI Payment -->
                <div class="p-3.5 bg-slate-900/80 rounded-2xl border border-slate-700/80">
                  <label class="flex items-center justify-between cursor-pointer gap-3">
                    <div>
                      <div class="flex items-center gap-2">
                        <i class="fa-solid fa-qrcode text-amber-400"></i>
                        <span class="text-xs font-bold text-white">Online UPI QR Payments</span>
                        <span
                          :class="[
                            'text-[10px] font-extrabold px-2 py-0.5 rounded-full',
                            settingsForm.enable_online_payment ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400'
                          ]"
                        >
                          {{ settingsForm.enable_online_payment ? 'ENABLED' : 'DISABLED' }}
                        </span>
                      </div>
                      <span class="text-[11px] text-slate-400 block mt-1">
                        Turn ON to allow customers to pay via UPI QR code. Turn OFF if you prefer customers to browse the digital menu and settle payment directly at the counter.
                      </span>
                    </div>
                    <input
                      type="checkbox"
                      v-model="settingsForm.enable_online_payment"
                      class="w-5 h-5 accent-amber-500 rounded cursor-pointer shrink-0"
                    />
                  </label>
                </div>

                <!-- Toggle 2: Cash / Pay at Counter -->
                <div class="p-3.5 bg-slate-900/80 rounded-2xl border border-slate-700/80">
                  <label class="flex items-center justify-between cursor-pointer gap-3">
                    <div>
                      <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hand-holding-dollar text-emerald-400"></i>
                        <span class="text-xs font-bold text-white">Pay at Counter / Cash to Staff</span>
                        <span
                          :class="[
                            'text-[10px] font-extrabold px-2 py-0.5 rounded-full',
                            settingsForm.enable_cash_payment ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400'
                          ]"
                        >
                          {{ settingsForm.enable_cash_payment ? 'ENABLED' : 'DISABLED' }}
                        </span>
                      </div>
                      <span class="text-[11px] text-slate-400 block mt-1">
                        Enable customers to pay directly via cash or card at the counter or their table.
                      </span>
                    </div>
                    <input
                      type="checkbox"
                      v-model="settingsForm.enable_cash_payment"
                      class="w-5 h-5 accent-amber-500 rounded cursor-pointer shrink-0"
                    />
                  </label>
                </div>
              </div>

              <!-- UPI Details (shown if Online Payment is enabled) -->
              <div v-if="settingsForm.enable_online_payment" class="bg-amber-500/5 p-4 rounded-2xl border border-amber-500/20 mb-4 space-y-3">
                <div class="text-xs font-bold text-amber-300 flex items-center gap-1.5">
                  <i class="fa-solid fa-wallet"></i> UPI Credentials Configuration
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">
                      Your UPI ID (VPA) *
                      <span class="text-[10px] text-amber-300">(Customers will pay to this ID)</span>
                    </label>
                    <input
                      v-model="settingsForm.upi_id"
                      type="text"
                      placeholder="e.g. yourname@okhdfcbank"
                      class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                    />
                  </div>

                  <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">UPI Payee Display Name</label>
                    <input
                      v-model="settingsForm.upi_name"
                      type="text"
                      placeholder="e.g. Royal Spice Restaurant"
                      class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                    />
                  </div>
                </div>
              </div>

              <!-- Notice if Online Payment is disabled -->
              <div v-else class="p-3 bg-slate-800/60 rounded-xl border border-slate-700 text-xs text-slate-400 flex items-center gap-2 mb-4">
                <i class="fa-solid fa-circle-info text-amber-400"></i>
                <span>Online payment is disabled. Customers will browse the digital menu and place orders, while payment will be settled in-person at the counter or table.</span>
              </div>

              <!-- Order Types & Fulfillment Options (Home Delivery & Takeaway) -->
              <div v-if="settingsForm.business_type !== 'salon'" class="pt-4 border-t border-slate-700/80 mb-4">
                <div class="flex items-center justify-between mb-3">
                  <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                    <i class="fa-solid fa-truck-ramp-box"></i> Order Types & Delivery Modes
                  </h3>
                </div>

                <div class="space-y-3">
                  <!-- Toggle: Home Delivery -->
                  <div class="p-3.5 bg-slate-900/80 rounded-2xl border border-slate-700/80">
                    <label class="flex items-center justify-between cursor-pointer gap-3">
                      <div>
                        <div class="flex items-center gap-2">
                          <i class="fa-solid fa-motorcycle text-blue-400"></i>
                          <span class="text-xs font-bold text-white">Home Delivery Service</span>
                          <span
                            :class="[
                              'text-[10px] font-extrabold px-2 py-0.5 rounded-full',
                              settingsForm.enable_home_delivery ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400'
                            ]"
                          >
                            {{ settingsForm.enable_home_delivery ? 'ENABLED' : 'DISABLED' }}
                          </span>
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-1">
                          Turn ON to let customers order from home/office and enter their delivery address. Turn OFF if your restaurant only does Dine-In.
                        </span>
                      </div>
                      <input
                        type="checkbox"
                        v-model="settingsForm.enable_home_delivery"
                        class="w-5 h-5 accent-blue-500 rounded cursor-pointer shrink-0"
                      />
                    </label>
                  </div>

                  <!-- Toggle: Takeaway / Pickup -->
                  <div class="p-3.5 bg-slate-900/80 rounded-2xl border border-slate-700/80">
                    <label class="flex items-center justify-between cursor-pointer gap-3">
                      <div>
                        <div class="flex items-center gap-2">
                          <i class="fa-solid fa-bag-shopping text-purple-400"></i>
                          <span class="text-xs font-bold text-white">Takeaway / Self-Pickup</span>
                          <span
                            :class="[
                              'text-[10px] font-extrabold px-2 py-0.5 rounded-full',
                              settingsForm.enable_takeaway ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400'
                            ]"
                          >
                            {{ settingsForm.enable_takeaway ? 'ENABLED' : 'DISABLED' }}
                          </span>
                        </div>
                        <span class="text-[11px] text-slate-400 block mt-1">
                          Allow customers to order takeaway parcels in advance and collect at your counter.
                        </span>
                      </div>
                      <input
                        type="checkbox"
                        v-model="settingsForm.enable_takeaway"
                        class="w-5 h-5 accent-purple-500 rounded cursor-pointer shrink-0"
                      />
                    </label>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                <div>
                  <label class="block text-xs font-semibold text-slate-300 mb-1">Tax / GST Percentage (%)</label>
                  <input
                    v-model.number="settingsForm.tax_percentage"
                    type="number"
                    step="0.1"
                    min="0"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                  />
                </div>

                <div>
                  <label class="block text-xs font-semibold text-slate-300 mb-1">Total Tables Count</label>
                  <input
                    v-model.number="settingsForm.table_count"
                    type="number"
                    min="1"
                    max="100"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                  />
                </div>
              </div>

              <!-- Table Policy Setting -->
              <div class="mt-4 p-3 bg-slate-900/60 rounded-2xl border border-slate-700/80">
                <label class="flex items-center justify-between cursor-pointer">
                  <div>
                    <span class="text-xs font-bold text-white block">Allow Multiple Orders on Same Table (Group Dining)</span>
                    <span class="text-[10px] text-slate-400">
                      When ON, multiple guests at the same table can place separate orders. When OFF, only one active order is permitted per table at a time.
                    </span>
                  </div>
                  <input
                    type="checkbox"
                    v-model="settingsForm.allow_shared_table_orders"
                    class="w-4 h-4 accent-amber-500 rounded cursor-pointer"
                  />
                </label>
              </div>
            </div>

            <!-- Address & Contact -->
            <div class="pt-4 border-t border-slate-700/80">
              <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Address & Media</h3>
              <div class="space-y-3">
                <div>
                  <label class="block text-xs font-semibold text-slate-300 mb-1">Address</label>
                  <input
                    v-model="settingsForm.address"
                    type="text"
                    class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
                  />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                  <!-- Logo Upload -->
                  <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-300">Restaurant Logo</label>
                    <div class="flex items-center gap-3">
                      <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shrink-0">
                        <img
                          v-if="settingsForm.logo_url"
                          :src="settingsForm.logo_url"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                          <i class="fa-solid fa-image"></i>
                        </div>
                      </div>
                      <div class="flex-1 space-y-1.5">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold cursor-pointer transition">
                          <i v-if="uploadingLogoImg" class="fa-solid fa-circle-notch fa-spin text-amber-400"></i>
                          <i v-else class="fa-solid fa-cloud-arrow-up text-amber-400"></i>
                          <span>{{ uploadingLogoImg ? 'Uploading...' : 'Choose File' }}</span>
                          <input
                            type="file"
                            accept="image/*"
                            class="hidden"
                            :disabled="uploadingLogoImg"
                            @change="e => handleFileUpload(e, 'logo', 'logos')"
                          />
                        </label>
                        <input
                          v-model="settingsForm.logo_url"
                          type="url"
                          placeholder="Or paste Logo URL"
                          class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none placeholder-slate-500"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Cover Upload -->
                  <div class="space-y-2">
                    <label class="block text-xs font-semibold text-slate-300">Cover Banner</label>
                    <div class="flex items-center gap-3">
                      <div class="w-20 h-14 rounded-xl overflow-hidden bg-slate-900 border border-slate-700 shrink-0">
                        <img
                          v-if="settingsForm.cover_url"
                          :src="settingsForm.cover_url"
                          class="w-full h-full object-cover"
                        />
                        <div v-else class="w-full h-full flex items-center justify-center text-slate-600">
                          <i class="fa-solid fa-panorama"></i>
                        </div>
                      </div>
                      <div class="flex-1 space-y-1.5">
                        <label class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-xl text-xs font-bold cursor-pointer transition">
                          <i v-if="uploadingCoverImg" class="fa-solid fa-circle-notch fa-spin text-amber-400"></i>
                          <i v-else class="fa-solid fa-cloud-arrow-up text-amber-400"></i>
                          <span>{{ uploadingCoverImg ? 'Uploading...' : 'Choose File' }}</span>
                          <input
                            type="file"
                            accept="image/*"
                            class="hidden"
                            :disabled="uploadingCoverImg"
                            @change="e => handleFileUpload(e, 'cover', 'covers')"
                          />
                        </label>
                        <input
                          v-model="settingsForm.cover_url"
                          type="url"
                          placeholder="Or paste Banner URL"
                          class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none placeholder-slate-500"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <button
              type="submit"
              :disabled="savingSettings"
              class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition flex items-center justify-center gap-2"
            >
              <i v-if="savingSettings" class="fa-solid fa-circle-notch fa-spin"></i>
              <span v-else>Save Restaurant Settings</span>
            </button>
          </form>
        </div>
      </div>
    </main>

    <!-- Modal: Add/Edit Dish / Service / Product -->
    <div
      v-if="showDishModal"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-slate-800 border border-slate-700 w-full max-w-md rounded-3xl p-6 shadow-2xl text-white">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700 mb-4">
          <h3 class="font-bold text-base">
            {{ editingDishId ? ('Edit ' + (restaurant?.business_type === 'salon' ? 'Service' : (restaurant?.business_type === 'retail' ? 'Product' : 'Dish'))) : ('Add New ' + (restaurant?.business_type === 'salon' ? 'Service' : (restaurant?.business_type === 'retail' ? 'Product' : 'Dish to Menu'))) }}
          </h3>
          <button @click="showDishModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="saveDish" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Category *</label>
            <select
              v-model="dishForm.category_id"
              required
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            >
              <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                {{ cat.name }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">
              {{ restaurant?.business_type === 'salon' ? 'Service Name *' : (restaurant?.business_type === 'retail' ? 'Product Name *' : 'Dish Name *') }}
            </label>
            <input
              v-model="dishForm.name"
              type="text"
              required
              :placeholder="restaurant?.business_type === 'salon' ? 'e.g. Classic Haircut & Styling' : (restaurant?.business_type === 'retail' ? 'e.g. Casual Denim Shirt' : 'e.g. Paneer Tikka Angara')"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Price ({{ restaurant.currency }}) *</label>
              <input
                v-model.number="dishForm.price"
                type="number"
                step="0.01"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              />
            </div>

            <!-- FOR FOOD: Food Type -->
            <div v-if="!restaurant?.business_type || restaurant?.business_type === 'food'">
              <label class="block text-xs font-semibold text-slate-300 mb-1">Food Type *</label>
              <select
                v-model="dishForm.food_type"
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              >
                <option value="veg">Vegetarian</option>
                <option value="non-veg">Non-Vegetarian</option>
                <option value="egg">Contains Egg</option>
                <option value="beverage">Beverage</option>
              </select>
            </div>

            <!-- FOR SALON: Service Duration -->
            <div v-else-if="restaurant?.business_type === 'salon'">
              <label class="block text-xs font-semibold text-slate-300 mb-1">Service Duration *</label>
              <select
                v-model.number="dishForm.duration_minutes"
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              >
                <option :value="15">⏱️ 15 Mins (Express)</option>
                <option :value="30">⏱️ 30 Mins (Standard)</option>
                <option :value="45">⏱️ 45 Mins</option>
                <option :value="60">⏱️ 60 Mins (1 Hour)</option>
                <option :value="90">⏱️ 90 Mins (1.5 Hr)</option>
                <option :value="120">⏱️ 120 Mins (2 Hours)</option>
              </select>
            </div>

            <!-- FOR RETAIL: Target Fit/Audience -->
            <div v-else-if="restaurant?.business_type === 'retail'">
              <label class="block text-xs font-semibold text-slate-300 mb-1">Department / Fit</label>
              <select
                v-model="dishForm.target_audience"
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              >
                <option value="all">Unisex / All</option>
                <option value="men">Men</option>
                <option value="women">Women</option>
                <option value="kids">Kids</option>
              </select>
            </div>
          </div>

          <!-- SALON: Target Client -->
          <div v-if="restaurant?.business_type === 'salon'">
            <label class="block text-xs font-semibold text-slate-300 mb-1">Target Client / Gender</label>
            <select
              v-model="dishForm.target_audience"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            >
              <option value="all">Unisex / Everyone</option>
              <option value="men">Men & Boys</option>
              <option value="women">Women & Girls</option>
              <option value="kids">Kids Only</option>
            </select>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Description</label>
            <textarea
              v-model="dishForm.description"
              rows="2"
              :placeholder="restaurant?.business_type === 'salon' ? 'Service details, styling procedure...' : (restaurant?.business_type === 'retail' ? 'Fabric, fit, care instructions...' : 'Ingredients and culinary style...')"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1.5 flex items-center justify-between">
              <span>{{ restaurant?.business_type === 'salon' ? 'Service Photo (Upload or Paste Link)' : (restaurant?.business_type === 'retail' ? 'Product Photo (Upload or Paste Link)' : 'Dish Photo (Upload or Paste Link)') }}</span>
              <span v-if="dishForm.image_url" class="text-[10px] text-amber-400 font-normal">Image selected</span>
            </label>

            <!-- Image Preview Thumbnail if selected -->
            <div v-if="dishForm.image_url" class="mb-2 relative rounded-xl overflow-hidden h-28 bg-slate-900 border border-slate-700 flex items-center justify-center">
              <img :src="dishForm.image_url" class="w-full h-full object-cover" />
              <button
                type="button"
                @click="dishForm.image_url = ''"
                class="absolute top-1.5 right-1.5 bg-rose-600/90 hover:bg-rose-600 text-white w-6 h-6 rounded-full flex items-center justify-center text-xs shadow"
                title="Remove image"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
              <!-- Upload from device button -->
              <label class="flex items-center justify-center gap-2 px-3 py-2 bg-slate-700 hover:bg-slate-600 border border-slate-600 rounded-xl text-xs font-bold text-white cursor-pointer transition">
                <i v-if="uploadingDishImg" class="fa-solid fa-circle-notch fa-spin text-amber-400"></i>
                <i v-else class="fa-solid fa-cloud-arrow-up text-amber-400"></i>
                <span>{{ uploadingDishImg ? 'Uploading...' : 'Choose File' }}</span>
                <input
                  type="file"
                  accept="image/*"
                  class="hidden"
                  :disabled="uploadingDishImg"
                  @change="e => handleFileUpload(e, 'dish', 'dishes')"
                />
              </label>

              <!-- URL input -->
              <input
                v-model="dishForm.image_url"
                type="url"
                placeholder="Or paste image URL"
                class="px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none"
              />
            </div>
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition mt-4"
          >
            {{ editingDishId ? ('Update ' + (restaurant?.business_type === 'salon' ? 'Service' : (restaurant?.business_type === 'retail' ? 'Product' : 'Dish'))) : ('Save ' + (restaurant?.business_type === 'salon' ? 'Service' : (restaurant?.business_type === 'retail' ? 'Product' : 'Dish'))) }}
          </button>
        </form>
      </div>
    </div>

    <!-- Modal: Add/Edit Category -->
    <div
      v-if="showCategoryModal"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-slate-800 border border-slate-700 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-white">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700 mb-4">
          <h3 class="font-bold text-base">{{ editingCategoryId ? 'Edit Category' : 'Add Category' }}</h3>
          <button @click="showCategoryModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="saveCategory" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Category Name *</label>
            <input
              v-model="categoryForm.name"
              type="text"
              required
              placeholder="e.g. Starters, Biryani, Desserts"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Description (Optional)</label>
            <input
              v-model="categoryForm.description"
              type="text"
              placeholder="e.g. Freshly cooked appetizers"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            />
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition mt-4"
          >
            {{ editingCategoryId ? 'Update Category' : 'Save Category' }}
          </button>
        </form>
      </div>
    </div>

    <!-- Modal: Add Single Custom Table -->
    <div
      v-if="showAddTableModal"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-slate-800 border border-slate-700 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-white">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700 mb-4">
          <h3 class="font-bold text-base">Add New Table / Dining Zone</h3>
          <button @click="showAddTableModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="saveNewTable" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Table Name *</label>
            <input
              v-model="newTableForm.name"
              type="text"
              required
              placeholder="e.g. Table 15, VIP Room 1, Rooftop 3"
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Short Code</label>
              <input
                v-model="newTableForm.code"
                type="text"
                placeholder="e.g. VIP1, T15"
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none font-mono"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Capacity (Seats) *</label>
              <input
                v-model.number="newTableForm.seating_capacity"
                type="number"
                min="1"
                max="50"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              />
            </div>
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition mt-4"
          >
            Create Table
          </button>
        </form>
      </div>
    </div>

    <!-- Modal: Bulk Generate Tables -->
    <div
      v-if="showBulkTableModal"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-4"
    >
      <div class="bg-slate-800 border border-slate-700 w-full max-w-sm rounded-3xl p-6 shadow-2xl text-white">
        <div class="flex items-center justify-between pb-3 border-b border-slate-700 mb-4">
          <h3 class="font-bold text-base">Bulk Generate Tables</h3>
          <button @click="showBulkTableModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="saveBulkTables" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Number of Tables to Create *</label>
            <input
              v-model.number="bulkTableForm.count"
              type="number"
              min="1"
              max="50"
              required
              class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Name Prefix *</label>
              <input
                v-model="bulkTableForm.prefix"
                type="text"
                placeholder="e.g. Table, VIP, Booth"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-slate-300 mb-1">Seating per Table</label>
              <input
                v-model.number="bulkTableForm.seating_capacity"
                type="number"
                min="1"
                max="50"
                required
                class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white focus:outline-none"
              />
            </div>
          </div>

          <button
            type="submit"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl text-xs shadow-lg transition mt-4"
          >
            Generate {{ bulkTableForm.count }} Tables
          </button>
        </form>
      </div>
    </div>

    <!-- Menu PDF Preview & Download Modal -->
    <div
      v-if="showPdfModal"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
      @click.self="showPdfModal = false"
    >
      <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Top Bar -->
        <div class="px-5 py-3.5 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800 shrink-0">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-400 border border-rose-500/30 flex items-center justify-center">
              <i class="fa-solid fa-file-pdf text-sm"></i>
            </div>
            <div>
              <h3 class="font-bold text-xs sm:text-sm leading-tight">{{ restaurant?.name }} - Digital Menu Card</h3>
              <p class="text-[10px] text-slate-400">Export high-resolution PDF or print physical menu</p>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="downloadPdf"
              :disabled="generatingPdf"
              class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow flex items-center gap-1.5 transition cursor-pointer"
            >
              <i v-if="generatingPdf" class="fa-solid fa-circle-notch fa-spin"></i>
              <i v-else class="fa-solid fa-download"></i>
              <span>{{ generatingPdf ? 'Saving PDF...' : 'Download PDF' }}</span>
            </button>

            <button
              @click="printMenu"
              class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs rounded-xl border border-slate-700 flex items-center gap-1.5 transition cursor-pointer"
              title="Print directly or save via browser"
            >
              <i class="fa-solid fa-print"></i>
              <span class="hidden sm:inline">Print</span>
            </button>

            <button
              @click="showPdfModal = false"
              class="w-7 h-7 rounded-full bg-slate-800 text-slate-400 hover:text-white hover:bg-slate-700 flex items-center justify-center transition"
            >
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </div>
        </div>

        <!-- Scrollable Menu Card Content -->
        <div class="p-4 sm:p-6 overflow-y-auto bg-slate-100 flex-1">
          <!-- The Printable Menu Card Element (captured by html2pdf and @media print) -->
          <div
            id="printable-menu-card"
            class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 max-w-xl mx-auto text-slate-900 font-sans"
          >
            <!-- Card Header -->
            <div class="text-center pb-5 mb-5 border-b-2 border-amber-500">
              <div class="flex items-center justify-center gap-3 mb-2">
                <img
                  v-if="restaurant?.logo_url"
                  :src="restaurant.logo_url"
                  class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm"
                />
                <div class="text-left">
                  <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    {{ restaurant?.name }}
                  </h1>
                  <p v-if="restaurant?.tagline" class="text-xs font-semibold text-amber-600">
                    {{ restaurant.tagline }}
                  </p>
                </div>
              </div>

              <p v-if="restaurant?.description" class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                {{ restaurant.description }}
              </p>

              <div class="text-[11px] text-slate-500 mt-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <span v-if="restaurant?.phone"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ restaurant.phone }}</span>
                <span v-if="restaurant?.email"><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ restaurant.email }}</span>
                <span v-if="restaurant?.address"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ restaurant.address }}</span>
              </div>

              <div class="inline-block mt-3 px-4 py-0.5 bg-amber-500 text-slate-950 font-extrabold text-[11px] tracking-widest uppercase rounded-full">
                ✦ RESTAURANT MENU ✦
              </div>
            </div>

            <!-- Menu Categories & Items -->
            <div class="space-y-5">
              <div v-for="cat in categoriesWithItems" :key="cat.id" class="space-y-2.5">
                <!-- Category Title Header -->
                <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
                  <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                  <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">{{ cat.name }}</h2>
                  <span class="text-[10px] text-slate-400 font-normal">({{ cat.items?.length || 0 }})</span>
                  <div class="flex-1 border-b border-dotted border-slate-300 ml-2"></div>
                </div>

                <!-- Dishes List -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                  <div
                    v-for="item in (cat.items || [])"
                    :key="item.id"
                    class="p-2 rounded-xl border border-slate-100 bg-slate-50/70 flex flex-col justify-between"
                  >
                    <div>
                      <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-1.5 flex-1">
                          <template v-if="!restaurant?.business_type || restaurant?.business_type === 'food'">
                            <span
                              v-if="item.food_type === 'veg'"
                              class="w-3 h-3 border border-emerald-600 rounded flex items-center justify-center p-0.5 shrink-0"
                              title="Veg"
                            >
                              <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                            </span>
                            <span
                              v-else
                              class="w-3 h-3 border border-rose-600 rounded flex items-center justify-center p-0.5 shrink-0"
                              title="Non-Veg"
                            >
                              <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                            </span>
                          </template>
                          <template v-else-if="restaurant?.business_type === 'salon'">
                            <span class="text-[10px] text-amber-600 font-bold shrink-0">✂️</span>
                          </template>
                          <template v-else>
                            <span class="text-[10px] text-indigo-600 font-bold shrink-0">🛍️</span>
                          </template>

                          <span class="font-bold text-xs text-slate-900 leading-snug">{{ item.name }}</span>
                          <span v-if="restaurant?.business_type === 'salon' && item.duration_minutes" class="text-[8px] bg-amber-100 text-amber-800 font-bold px-1 rounded">⏱️ {{ item.duration_minutes }}m</span>
                          <span v-if="item.is_recommended" class="text-[8px] bg-amber-100 text-amber-800 font-bold px-1 rounded">⭐</span>
                        </div>

                        <span class="font-extrabold text-xs text-amber-600 whitespace-nowrap">
                          {{ restaurant?.currency }}{{ item.price }}
                        </span>
                      </div>

                      <p v-if="item.description" class="text-[9px] text-slate-500 mt-1 line-clamp-2">
                        {{ item.description }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Footer with QR and Info -->
            <div class="mt-6 pt-4 border-t-2 border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 text-center sm:text-left">
              <div class="space-y-0.5">
                <div class="text-xs font-bold text-slate-800">Thank you for dining with us!</div>
                <div class="text-[10px] text-slate-500">
                  * Taxes ({{ restaurant?.tax_percentage }}% GST) applicable as per menu policy.
                </div>
                <div class="text-[9px] text-slate-400 font-mono truncate max-w-xs">
                  {{ windowOrigin }}/r/{{ restaurant?.slug }}
                </div>
              </div>

              <div v-if="menuPdfQrCode" class="flex items-center gap-2 bg-slate-50 p-1.5 rounded-xl border border-slate-200 shrink-0">
                <img :src="menuPdfQrCode" class="w-12 h-12 rounded-lg object-contain" />
                <div class="text-[9px] text-slate-600 text-left font-semibold leading-tight max-w-[90px]">
                  Scan to view digital menu & order
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';
import QRCode from 'qrcode';
import html2pdf from 'html2pdf.js';
import { playSound, sendSystemNotification, requestNotificationPermission } from '../utils/notification';

const router = useRouter();

const loading = ref(true);
const activeTab = ref('overview');
const restaurant = ref(null);
const stats = ref({});
const orders = ref([]);
const categories = ref([]);
const menuItems = ref([]);
const orderStatusFilter = ref('all');
const updatingOrderId = ref(null);
const newOrderToast = ref(null);
const notificationsEnabled = ref(false);

const toggleNotificationAlerts = async () => {
  playSound('new_order');
  await requestNotificationPermission();
  notificationsEnabled.value = true;
};

// Modals
const showDishModal = ref(false);
const editingDishId = ref(null);
const dishForm = ref({
  category_id: '',
  name: '',
  price: 0,
  food_type: 'veg',
  duration_minutes: 30,
  target_audience: 'all',
  description: '',
  image_url: '',
  is_available: true,
});

const showCategoryModal = ref(false);
const editingCategoryId = ref(null);
const categoryForm = ref({
  name: '',
  description: '',
});

// Menu PDF Modal state & generator
const showPdfModal = ref(false);
const generatingPdf = ref(false);
const menuPdfQrCode = ref('');
const windowOrigin = computed(() => typeof window !== 'undefined' ? window.location.origin : '');

const categoriesWithItems = computed(() => {
  return categories.value.map(cat => ({
    ...cat,
    items: menuItems.value.filter(item => item.category_id === cat.id)
  }));
});

const openPdfModal = async () => {
  showPdfModal.value = true;
  if (!menuPdfQrCode.value && restaurant.value) {
    try {
      const url = `${window.location.origin}/r/${restaurant.value.slug}`;
      menuPdfQrCode.value = await QRCode.toDataURL(url, {
        width: 140,
        margin: 1,
        color: { dark: '#0f172a', light: '#ffffff' },
      });
    } catch (err) {
      console.error('QR error', err);
    }
  }
};

const downloadPdf = async () => {
  generatingPdf.value = true;
  try {
    const element = document.getElementById('printable-menu-card');
    if (!element) return;

    const opt = {
      margin: [6, 6, 6, 6],
      filename: `${restaurant.value?.slug || 'restaurant'}-menu.pdf`,
      image: { type: 'jpeg', quality: 0.98 },
      html2canvas: { scale: 2, useCORS: true, logging: false },
      jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
    };

    await html2pdf().set(opt).from(element).save();
  } catch (err) {
    console.error('PDF export error', err);
    window.print();
  } finally {
    generatingPdf.value = false;
  }
};

const printMenu = () => {
  window.print();
};

// Settings form
const settingsForm = ref({});
const savingSettings = ref(false);
const settingsSuccessMsg = ref('');

// Upload state refs
const uploadingDishImg = ref(false);
const uploadingLogoImg = ref(false);
const uploadingCoverImg = ref(false);

const handleFileUpload = async (event, targetField, folder = 'dishes') => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('image', file);
  formData.append('folder', folder);

  if (targetField === 'dish') uploadingDishImg.value = true;
  else if (targetField === 'logo') uploadingLogoImg.value = true;
  else if (targetField === 'cover') uploadingCoverImg.value = true;

  try {
    const res = await axios.post('/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data.success) {
      if (targetField === 'dish') {
        dishForm.value.image_url = res.data.url;
      } else if (targetField === 'logo') {
        settingsForm.value.logo_url = res.data.url;
      } else if (targetField === 'cover') {
        settingsForm.value.cover_url = res.data.url;
      }
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to upload image. Please ensure it is an image file under 5MB.');
  } finally {
    if (targetField === 'dish') uploadingDishImg.value = false;
    else if (targetField === 'logo') uploadingLogoImg.value = false;
    else if (targetField === 'cover') uploadingCoverImg.value = false;
    event.target.value = '';
  }
};

// Dynamic Tables state
const tables = ref([]);
const tableStats = ref({ total: 0, available: 0, occupied: 0 });

const showAddTableModal = ref(false);
const newTableForm = ref({
  name: '',
  code: '',
  seating_capacity: 4,
});

const showBulkTableModal = ref(false);
const bulkTableForm = ref({
  count: 10,
  prefix: 'Table',
  seating_capacity: 4,
});

// QR Code tab
const selectedTableNumber = ref('Table 1');
const qrDataUrl = ref('');

const tabs = computed(() => {
  const isSalon = restaurant.value?.business_type === 'salon';
  return [
    { id: 'overview', name: 'Overview', icon: 'fa-solid fa-chart-pie' },
    {
      id: 'orders',
      name: isSalon ? 'Appointments & Queue' : 'Live Orders',
      icon: isSalon ? 'fa-solid fa-calendar-check' : 'fa-solid fa-fire-burner',
      badge: stats.value.pending_orders_count || 0
    },
    {
      id: 'tables',
      name: isSalon ? 'Chairs & Stylists' : 'Floor & Tables',
      icon: isSalon ? 'fa-solid fa-chair' : 'fa-solid fa-chair',
      badge: tableStats.value.occupied || 0
    },
    {
      id: 'qrcodes',
      name: isSalon ? 'Shop QR Standee' : 'Table QR Generator',
      icon: 'fa-solid fa-qrcode'
    },
    {
      id: 'menu',
      name: isSalon ? 'Services & Rates' : 'Menu Dishes',
      icon: isSalon ? 'fa-solid fa-scissors' : 'fa-solid fa-utensils'
    },
    { id: 'categories', name: 'Categories', icon: 'fa-solid fa-list' },
    { id: 'settings', name: 'Settings & Timings', icon: 'fa-solid fa-gear' },
  ];
});

const currentTableUrl = computed(() => {
  if (!restaurant.value) return '';
  const origin = window.location.origin;
  if (selectedTableNumber.value === 'main') {
    return `${origin}/r/${restaurant.value.slug}`;
  }
  return `${origin}/r/${restaurant.value.slug}?table=${selectedTableNumber.value}`;
});

const generateQrCode = async () => {
  if (!currentTableUrl.value) return;
  try {
    qrDataUrl.value = await QRCode.toDataURL(currentTableUrl.value, {
      width: 300,
      margin: 1,
      color: {
        dark: '#0f172a',
        light: '#ffffff',
      },
      errorCorrectionLevel: 'H',
    });
  } catch (err) {
    console.error('QR render error', err);
  }
};

// Re-generate QR whenever URL changes
watch(currentTableUrl, () => {
  generateQrCode();
}, { immediate: true });

// Lazy load dishes and categories when their tabs are opened
watch(activeTab, (tab) => {
  if (tab === 'dishes' && menuItems.value.length === 0) {
    fetchMenuItems();
    if (categories.value.length === 0) fetchCategories();
  } else if (tab === 'categories' && categories.value.length === 0) {
    fetchCategories();
  }
});

const fetchDashboardData = async () => {
  try {
    const res = await axios.get('/owner/restaurant');
    if (res.data.success) {
      restaurant.value = res.data.restaurant;
      stats.value = res.data.stats;
      settingsForm.value = { ...res.data.restaurant };

      await Promise.all([
        fetchOrders(),
        fetchTables(),
      ]);

      generateQrCode();
    }
  } catch (err) {
    if (err.response?.status === 401) {
      router.push('/owner/login');
    }
  } finally {
    loading.value = false;
  }
};

let toastTimer = null;
const showNewOrderToast = (order) => {
  newOrderToast.value = order;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    newOrderToast.value = null;
  }, 6000);
};

const fetchOrders = async (silent = false) => {
  try {
    const params = {};
    if (orderStatusFilter.value !== 'all') {
      params.status = orderStatusFilter.value;
    }
    const res = await axios.get('/owner/orders', { params });
    if (res.data.success) {
      const incoming = res.data.orders.data || res.data.orders || [];

      // Detect New Orders & Payment Completed on Existing Orders
      if (orders.value.length > 0 && incoming.length > 0) {
        const existingMap = new Map(orders.value.map(o => [o.id, o]));

        // 1. Brand New Order Detected
        const brandNewOrders = incoming.filter(o => !existingMap.has(o.id));
        if (brandNewOrders.length > 0) {
          const newest = brandNewOrders[0];
          playSound('new_order');
          sendSystemNotification('New Order Received! 🛎️', `#${newest.order_code} • ${newest.table_number || 'Delivery'} (${restaurant.value?.currency || '₹'}${newest.total_amount})`);
          showNewOrderToast({
            type: 'new_order',
            title: 'NEW ORDER RECEIVED!',
            code: newest.order_code,
            location: newest.table_number || (newest.order_type === 'delivery' ? 'Home Delivery' : 'Takeaway'),
            amount: newest.total_amount,
            customer: `${newest.customer_name} (${newest.customer_phone})`,
          });
        }

        // 2. Payment Completed Detected
        for (const inc of incoming) {
          const old = existingMap.get(inc.id);
          if (old && old.payment_status !== 'paid' && inc.payment_status === 'paid') {
            playSound('payment_success');
            sendSystemNotification('Payment Completed! 💰', `#${inc.order_code} • ${restaurant.value?.currency || '₹'}${inc.total_amount} paid successfully`);
            showNewOrderToast({
              type: 'payment',
              title: 'PAYMENT COMPLETED!',
              code: inc.order_code,
              location: inc.table_number || 'Order',
              amount: inc.total_amount,
              customer: `${inc.customer_name} paid via UPI / Cash`,
            });
            break;
          }
        }
      }

      orders.value = incoming;

      // Update active orders count in stats
      const pendingOrActiveCount = incoming.filter(o => ['pending', 'accepted', 'preparing', 'ready'].includes(o.order_status)).length;
      if (stats.value) {
        stats.value.active_orders_count = pendingOrActiveCount;
      }
    }
  } catch (err) {
    if (!silent) console.error('Failed to load orders', err);
  }
};

// Automatic Real-Time Polling for New Orders
let orderPollTimer = null;
let isPolling = false;

const startAutoPoll = () => {
  stopAutoPoll();
  orderPollTimer = setInterval(async () => {
    // Only poll when tab is visible and not already running an active request
    if (document.hidden || isPolling) return;
    isPolling = true;
    try {
      await fetchOrders(true);
    } finally {
      isPolling = false;
    }
  }, 4000); // Polls every 4 seconds
};

const stopAutoPoll = () => {
  if (orderPollTimer) {
    clearInterval(orderPollTimer);
    orderPollTimer = null;
  }
};

const fetchCategories = async () => {
  try {
    const res = await axios.get('/owner/categories');
    if (res.data.success) {
      categories.value = res.data.categories;
      if (categories.value.length > 0 && !dishForm.value.category_id) {
        dishForm.value.category_id = categories.value[0].id;
      }
    }
  } catch (err) {
    console.error('Failed to load categories', err);
  }
};

const fetchMenuItems = async () => {
  try {
    const res = await axios.get('/owner/menu-items');
    if (res.data.success) {
      menuItems.value = res.data.items;
    }
  } catch (err) {
    console.error('Failed to load menu items', err);
  }
};

// Dynamic Table Management Methods
const fetchTables = async () => {
  try {
    const res = await axios.get('/owner/tables');
    if (res.data.success) {
      tables.value = res.data.tables;
      tableStats.value = res.data.stats;
      if (tables.value.length > 0 && selectedTableNumber.value === 'Table 1') {
        selectedTableNumber.value = tables.value[0].name;
      }
    }
  } catch (err) {
    console.error('Failed to load tables', err);
  }
};

const vacateTable = async (tableId) => {
  const tbl = tables.value.find(t => t.id === tableId);
  if (tbl) {
    tbl.status = 'available';
    tbl.active_orders = [];
    tbl.orders_count = 0;
    tbl.table_total_amount = 0;
  }
  try {
    await axios.patch(`/owner/tables/${tableId}/vacate`);
  } catch (err) {
    console.error('Failed to vacate table', err);
    await fetchTables();
  }
};

const deleteTable = async (tableId) => {
  if (!confirm('Are you sure you want to delete this table?')) return;
  const origTables = [...tables.value];
  tables.value = tables.value.filter(t => t.id !== tableId);
  try {
    await axios.delete(`/owner/tables/${tableId}`);
  } catch (err) {
    tables.value = origTables;
    console.error('Failed to delete table', err);
    alert('Failed to delete table.');
  }
};

const saveNewTable = async () => {
  try {
    const res = await axios.post('/owner/tables', newTableForm.value);
    if (res.data.success) {
      showAddTableModal.value = false;
      newTableForm.value = { name: '', code: '', seating_capacity: 4 };
      if (res.data.table) {
        tables.value.push({
          ...res.data.table,
          status: 'available',
          active_orders: [],
          orders_count: 0,
          table_total_amount: 0,
        });
      } else {
        await fetchTables();
      }
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to add table');
  }
};

const saveBulkTables = async () => {
  try {
    const res = await axios.post('/owner/tables/bulk-generate', bulkTableForm.value);
    if (res.data.success) {
      showBulkTableModal.value = false;
      await fetchTables();
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to generate tables');
  }
};

// Order Status Updates (Optimistic UI - 0ms instant response)
const updateOrderStatus = async (orderId, newStatus) => {
  const orderList = Array.isArray(orders.value) ? orders.value : (orders.value?.data || []);
  const target = orderList.find(o => o.id === orderId);
  const oldStatus = target ? target.order_status : null;

  // 1. Immediately update UI in 0ms!
  if (target) {
    target.order_status = newStatus;
  }

  // Update table in local memory if order is completed or cancelled
  if (target && (newStatus === 'completed' || newStatus === 'cancelled')) {
    const tableMatch = tables.value.find(t => t.name === target.table_number);
    if (tableMatch) {
      tableMatch.orders_count = Math.max(0, (tableMatch.orders_count || 1) - 1);
      if (tableMatch.orders_count === 0) {
        tableMatch.status = 'available';
      }
    }
    if (stats.value && stats.value.active_orders_count > 0) {
      stats.value.active_orders_count--;
    }
  }

  updatingOrderId.value = orderId;

  // 2. Background sync (single PATCH request, no re-fetching overhead)
  try {
    const res = await axios.patch(`/owner/orders/${orderId}/status`, {
      order_status: newStatus,
    });
    if (res.data.success && res.data.order && target) {
      Object.assign(target, res.data.order);
    }
  } catch (err) {
    if (target && oldStatus) {
      target.order_status = oldStatus;
    }
    console.error('Failed to update status', err);
  } finally {
    updatingOrderId.value = null;
  }
};

const markOrderPaid = async (orderId) => {
  const orderList = Array.isArray(orders.value) ? orders.value : (orders.value?.data || []);
  const target = orderList.find(o => o.id === orderId);
  const oldPaidStatus = target ? target.payment_status : 'pending';

  // 1. Immediately update UI in 0ms!
  if (target) {
    target.payment_status = 'paid';
  }

  updatingOrderId.value = orderId;

  // 2. Background sync
  try {
    const res = await axios.patch(`/owner/orders/${orderId}/status`, {
      payment_status: 'paid',
    });
    if (res.data.success && res.data.order && target) {
      Object.assign(target, res.data.order);
    }
  } catch (err) {
    if (target) {
      target.payment_status = oldPaidStatus;
    }
    console.error('Failed to mark paid', err);
  } finally {
    updatingOrderId.value = null;
  }
};

// Menu Item Operations
const openDishModal = (dish) => {
  if (dish) {
    editingDishId.value = dish.id;
    dishForm.value = {
      ...dish,
      duration_minutes: dish.duration_minutes || 30,
      target_audience: dish.target_audience || 'all',
    };
  } else {
    editingDishId.value = null;
    dishForm.value = {
      category_id: categories.value[0]?.id || '',
      name: '',
      price: 150,
      food_type: restaurant.value?.business_type === 'food' ? 'veg' : null,
      duration_minutes: 30,
      target_audience: 'all',
      description: '',
      image_url: '',
      is_available: true,
    };
  }
  showDishModal.value = true;
};

const saveDish = async () => {
  try {
    if (editingDishId.value) {
      const res = await axios.put(`/owner/menu-items/${editingDishId.value}`, dishForm.value);
      if (res.data.success && res.data.item) {
        const idx = menuItems.value.findIndex(i => i.id === editingDishId.value);
        if (idx !== -1) {
          menuItems.value[idx] = res.data.item;
        }
      }
    } else {
      const res = await axios.post('/owner/menu-items', dishForm.value);
      if (res.data.success && res.data.item) {
        menuItems.value.unshift(res.data.item);
      }
    }
    showDishModal.value = false;
  } catch (err) {
    console.error('Failed to save dish', err);
    alert(err.response?.data?.message || 'Failed to save item');
  }
};

const toggleAvailability = async (id) => {
  const item = menuItems.value.find(i => i.id === id);
  if (!item) return;
  // Instant optimistic update (0ms delay)
  item.is_available = !item.is_available;
  try {
    await axios.patch(`/owner/menu-items/${id}/toggle-availability`);
  } catch (err) {
    item.is_available = !item.is_available;
    console.error('Failed to toggle availability', err);
  }
};

const deleteMenuItem = async (id) => {
  if (!confirm('Are you sure you want to delete this menu dish?')) return;
  const original = [...menuItems.value];
  // Instant optimistic remove (0ms delay)
  menuItems.value = menuItems.value.filter(i => i.id !== id);
  try {
    await axios.delete(`/owner/menu-items/${id}`);
  } catch (err) {
    menuItems.value = original;
    console.error('Failed to delete item', err);
    alert('Failed to delete item.');
  }
};

// Category Operations
const openCategoryModal = (cat) => {
  if (cat) {
    editingCategoryId.value = cat.id;
    categoryForm.value = { name: cat.name, description: cat.description };
  } else {
    editingCategoryId.value = null;
    categoryForm.value = { name: '', description: '' };
  }
  showCategoryModal.value = true;
};

const saveCategory = async () => {
  try {
    if (editingCategoryId.value) {
      const res = await axios.put(`/owner/categories/${editingCategoryId.value}`, categoryForm.value);
      if (res.data.success && res.data.category) {
        const idx = categories.value.findIndex(c => c.id === editingCategoryId.value);
        if (idx !== -1) {
          categories.value[idx] = { ...categories.value[idx], ...res.data.category };
        }
      }
    } else {
      const res = await axios.post('/owner/categories', categoryForm.value);
      if (res.data.success && res.data.category) {
        categories.value.push(res.data.category);
      }
    }
    showCategoryModal.value = false;
  } catch (err) {
    console.error('Failed to save category', err);
    alert(err.response?.data?.message || 'Failed to save category');
  }
};

const deleteCategory = async (id) => {
  if (!confirm('Are you sure you want to delete this category? All its dishes will also be removed.')) return;
  const origCats = [...categories.value];
  const origItems = [...menuItems.value];
  // Instant optimistic remove (0ms delay)
  categories.value = categories.value.filter(c => c.id !== id);
  menuItems.value = menuItems.value.filter(i => i.category_id !== id);
  try {
    await axios.delete(`/owner/categories/${id}`);
  } catch (err) {
    categories.value = origCats;
    menuItems.value = origItems;
    console.error('Failed to delete category', err);
    alert('Failed to delete category.');
  }
};

const printQrCode = () => {
  window.print();
};

// Update Restaurant Settings
const updateRestaurantSettings = async () => {
  savingSettings.value = true;
  settingsSuccessMsg.value = '';
  try {
    const res = await axios.put('/owner/restaurant', settingsForm.value);
    if (res.data.success) {
      restaurant.value = res.data.restaurant;
      settingsSuccessMsg.value = 'Settings and payment options updated successfully!';
      setTimeout(() => { settingsSuccessMsg.value = ''; }, 4000);
    }
  } catch (err) {
    console.error('Failed to update settings', err);
  } finally {
    savingSettings.value = false;
  }
};

const handleLogout = async () => {
  try {
    await axios.post('/auth/logout');
    router.push('/owner/login');
  } catch (err) {
    router.push('/owner/login');
  }
};

const getOrderBadge = (status) => {
  switch (status) {
    case 'pending': return 'bg-amber-500/20 text-amber-300 border-amber-500/30';
    case 'accepted': return 'bg-blue-500/20 text-blue-300 border-blue-500/30';
    case 'preparing': return 'bg-orange-500/20 text-orange-300 border-orange-500/30';
    case 'ready': return 'bg-purple-500/20 text-purple-300 border-purple-500/30';
    case 'served':
    case 'completed': return 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
    default: return 'bg-slate-700 text-slate-300';
  }
};

const formatOrderTime = (dateStr) => {
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
    const dateFormatted = d.toLocaleDateString([], { month: 'short', day: 'numeric' });
    return `${dateFormatted}, ${timeStr}`;
  }
};

onMounted(() => {
  fetchDashboardData();
  startAutoPoll();
});

onUnmounted(() => {
  stopAutoPoll();
});
</script>

<style scoped>
.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

@media print {
  body * {
    visibility: hidden;
  }
  #printable-qr, #printable-qr *,
  #printable-menu-card, #printable-menu-card * {
    visibility: visible;
  }
  #printable-qr {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    box-shadow: none !important;
  }
  #printable-menu-card {
    position: absolute;
    left: 0;
    top: 0;
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 16px !important;
    border: none !important;
    box-shadow: none !important;
  }
}
</style>
