<template>
  <div class="min-h-screen bg-slate-100 text-slate-800 pb-28">
    <!-- Top Header / Restaurant Banner -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="text-center">
        <i class="fa-solid fa-utensils fa-bounce text-4xl text-amber-500 mb-3"></i>
        <p class="text-slate-500 font-medium">Opening restaurant menu...</p>
      </div>
    </div>

    <div v-else-if="!restaurant" class="min-h-screen flex items-center justify-center p-4">
      <div class="bg-white p-8 rounded-2xl shadow-xl max-w-md text-center">
        <i class="fa-solid fa-store-slash text-5xl text-rose-500 mb-4"></i>
        <h2 class="text-2xl font-bold text-slate-800 mb-2">Restaurant Not Found</h2>
        <p class="text-sm text-slate-500 mb-6">The requested QR menu is unavailable or the restaurant is inactive.</p>
        <router-link to="/" class="px-5 py-2.5 bg-amber-500 text-slate-900 font-bold rounded-xl text-sm">
          Return to Home
        </router-link>
      </div>
    </div>

    <div v-else>
      <!-- Hero Banner -->
      <div class="relative bg-slate-900 text-white">
        <div class="h-44 sm:h-56 w-full overflow-hidden relative">
          <img
            :src="restaurant.cover_url || 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&fit=crop'"
            :alt="restaurant.name"
            class="w-full h-full object-cover opacity-80"
          />
          <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent"></div>
        </div>

        <div class="max-w-3xl mx-auto px-4 -mt-16 pb-4 relative z-10">
          <div class="flex items-end gap-4">
            <img
              :src="restaurant.logo_url || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=200&fit=crop'"
              class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-4 border-white shadow-xl bg-white"
            />
            <div class="flex-1 pb-1 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
              <div>
                <div class="flex flex-wrap items-center gap-2">
                  <h1 class="text-xl sm:text-2xl font-extrabold text-white leading-tight">
                    {{ restaurant.name }}
                  </h1>
                  <span class="text-[11px] bg-emerald-500/20 text-emerald-300 font-bold px-2 py-0.5 rounded-full border border-emerald-500/40">
                    <i class="fa-solid fa-circle text-[6px] mr-1"></i> Open
                  </span>
                </div>
                <p class="text-xs text-amber-300 font-medium mt-0.5">{{ restaurant.tagline }}</p>
              </div>

              <!-- Download Menu / Rate Card PDF Button -->
              <button
                @click="openPdfModal"
                class="px-3.5 py-2 bg-white/15 hover:bg-white/25 text-white rounded-xl text-xs font-bold border border-white/25 flex items-center gap-2 transition shrink-0 backdrop-blur-md self-start sm:self-center shadow cursor-pointer"
                :title="restaurant.business_type === 'salon' ? 'Download or Print complete Salon Rate Card as PDF' : 'Download or Print complete Restaurant Menu as PDF'"
              >
                <i class="fa-solid fa-file-pdf text-rose-400 text-sm"></i>
                <span>{{ restaurant.business_type === 'salon' ? 'Download Rate Card PDF' : (restaurant.business_type === 'retail' ? 'Download Catalog PDF' : 'Download Menu PDF') }}</span>
              </button>
            </div>
          </div>

          <!-- Salon / Barbershop Mode: Queue & Appointment Slot Banner -->
          <div v-if="restaurant.business_type === 'salon'" class="mt-4 pt-3 border-t border-slate-700/60">
            <div class="bg-gradient-to-r from-cyan-950/70 via-slate-900 to-slate-900 border border-cyan-500/30 p-3 sm:p-4 rounded-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 border border-cyan-500/30 flex items-center justify-center text-lg font-bold shrink-0">
                  <i class="fa-solid fa-scissors"></i>
                </div>
                <div>
                  <div class="font-extrabold text-white text-sm flex items-center gap-2">
                    <span>Book Haircut Slot</span>
                    <span class="bg-cyan-500 text-slate-950 text-[10px] px-2 py-0.2 rounded-full font-bold">Zero Waiting</span>
                  </div>
                  <div class="text-[11px] text-slate-300 mt-0.5 flex items-center gap-2 flex-wrap">
                    <span>Now Serving: <strong class="text-cyan-400">Token #{{ salonQueue.now_serving_token || 1 }}</strong></span>
                    <span class="text-slate-600">•</span>
                    <span>Total Booked Today: <strong class="text-white">{{ salonQueue.total_booked_today || 0 }}</strong></span>
                  </div>
                </div>
              </div>

              <!-- Time Slot Button -->
              <button
                @click="openSlotModal"
                class="px-4 py-2 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-extrabold text-xs rounded-xl shadow-lg shadow-cyan-500/20 transition flex items-center justify-center gap-2 cursor-pointer self-start sm:self-center"
              >
                <i class="fa-solid fa-calendar-check text-sm"></i>
                <span>{{ selectedBookingTime ? `${formatSlotDate(selectedBookingDate)} at ${selectedBookingTimeDisplay}` : 'Select Time Slot' }}</span>
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
              </button>
            </div>
          </div>

          <!-- Restaurant Mode: Order Type Switcher & Table Indicator -->
          <div v-else class="mt-4 pt-3 border-t border-slate-700/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
            <!-- Order Type Toggle -->
            <div class="flex items-center gap-1.5 bg-slate-800/90 p-1 rounded-xl border border-slate-700 self-start">
              <button
                @click="setOrderType('dine_in')"
                :class="[
                  'px-3 py-1.5 rounded-lg font-bold text-xs transition flex items-center gap-1.5',
                  orderType === 'dine_in' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-300 hover:text-white'
                ]"
              >
                <i class="fa-solid fa-utensils text-[11px]"></i> Dine-In
              </button>

              <button
                @click="setOrderType('delivery')"
                :class="[
                  'px-3 py-1.5 rounded-lg font-bold text-xs transition flex items-center gap-1.5',
                  orderType === 'delivery' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-300 hover:text-white'
                ]"
              >
                <i class="fa-solid fa-motorcycle text-[11px]"></i> Home Delivery
              </button>

              <button
                @click="setOrderType('takeaway')"
                :class="[
                  'px-3 py-1.5 rounded-lg font-bold text-xs transition flex items-center gap-1.5',
                  orderType === 'takeaway' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-300 hover:text-white'
                ]"
              >
                <i class="fa-solid fa-bag-shopping text-[11px]"></i> Takeaway
              </button>
            </div>

            <!-- Current Dining Location Pill -->
            <div class="flex items-center gap-2">
              <div v-if="orderType === 'dine_in'" class="flex items-center gap-1.5 bg-amber-500/20 text-amber-300 px-3 py-1.5 rounded-xl border border-amber-500/40 font-semibold">
                <i class="fa-solid fa-chair text-xs"></i>
                <span>{{ currentTable || 'Select Table' }}</span>
                <button @click="showChangeTableModal = true" class="ml-1 text-[11px] text-white underline hover:text-amber-200 font-bold cursor-pointer">
                  Change
                </button>
              </div>

              <div v-else-if="orderType === 'delivery'" class="flex items-center gap-1.5 bg-blue-500/20 text-blue-300 px-3 py-1.5 rounded-xl border border-blue-500/40 font-semibold">
                <i class="fa-solid fa-house-chimney text-xs"></i>
                <span>Order from Home</span>
              </div>

              <div v-else class="flex items-center gap-1.5 bg-purple-500/20 text-purple-300 px-3 py-1.5 rounded-xl border border-purple-500/40 font-semibold">
                <i class="fa-solid fa-shop text-xs"></i>
                <span>Pick up at Counter</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Sticky Navigation & Filters -->
      <div class="sticky top-0 z-30 bg-white/95 backdrop-blur-md shadow-sm border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 py-2.5">
          <!-- Search & Veg/Non-Veg Filter Row -->
          <div class="flex items-center gap-2 mb-2.5">
            <div class="relative flex-1">
              <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
              <input
                v-model="searchQuery"
                type="text"
                :placeholder="restaurant.business_type === 'salon' ? 'Search haircut, styling, beard, facial, spa...' : (restaurant.business_type === 'retail' ? 'Search shirts, jeans, footwear...' : 'Search dishes, drinks, starters...')"
                class="w-full pl-8 pr-3 py-2 bg-slate-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/30 border border-slate-200"
              />
              <button
                v-if="searchQuery"
                @click="searchQuery = ''"
                class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"
              >
                <i class="fa-solid fa-xmark"></i>
              </button>
            </div>

            <!-- Veg Only Toggle (Only visible for Food & Dining) -->
            <button
              v-if="!restaurant?.business_type || restaurant?.business_type === 'food'"
              @click="toggleVegOnly"
              :class="[
                'px-3 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition border shrink-0',
                vegOnly ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-slate-100 text-slate-600 border-slate-200'
              ]"
            >
              <span class="w-2.5 h-2.5 rounded-full border border-current flex items-center justify-center p-0.5">
                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
              </span>
              Veg Only
            </button>
          </div>

          <!-- Category Pills Horizontal Scroll -->
          <div class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <button
              @click="selectedCategory = 'all'"
              :class="[
                'px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition',
                selectedCategory === 'all'
                  ? 'bg-amber-500 text-slate-950 shadow-sm'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              ]"
            >
              {{ restaurant.business_type === 'salon' ? 'All Services' : (restaurant.business_type === 'retail' ? 'All Products' : 'All Items') }}
            </button>
            <button
              v-for="cat in restaurant.categories"
              :key="cat.id"
              @click="selectedCategory = cat.id"
              :class="[
                'px-3.5 py-1.5 rounded-full text-xs font-semibold whitespace-nowrap transition',
                selectedCategory === cat.id
                  ? 'bg-amber-500 text-slate-950 shadow-sm'
                  : 'bg-slate-100 text-slate-600 hover:bg-slate-200'
              ]"
            >
              {{ cat.name }} ({{ cat.menu_items ? cat.menu_items.length : 0 }})
            </button>
          </div>
        </div>
      </div>

      <!-- Menu Items List -->
      <div class="max-w-3xl mx-auto px-4 py-6">
        <div v-if="filteredCategories.length === 0" class="text-center py-12 bg-white rounded-2xl shadow-sm">
          <i
            :class="[
              'text-4xl text-slate-300 mb-2 fa-solid',
              restaurant.business_type === 'salon' ? 'fa-scissors' : (restaurant.business_type === 'retail' ? 'fa-bag-shopping' : 'fa-bowl-food')
            ]"
          ></i>
          <p class="text-slate-500 text-sm font-medium">
            {{ restaurant.business_type === 'salon' ? 'No services match your search.' : (restaurant.business_type === 'retail' ? 'No products match your search.' : 'No dishes match your filter.') }}
          </p>
          <button @click="resetFilters" class="mt-3 text-xs text-amber-600 font-bold underline">
            Reset Filters
          </button>
        </div>

        <div v-else class="space-y-8">
          <div v-for="cat in filteredCategories" :key="cat.id" :id="`cat-${cat.id}`" class="space-y-3">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
              <div>
                <h2 class="text-lg font-bold text-slate-900">{{ cat.name }}</h2>
                <p v-if="cat.description" class="text-xs text-slate-500">{{ cat.description }}</p>
              </div>
              <span class="text-xs text-slate-400 font-medium">
                {{ cat.menu_items.length }} {{ restaurant.business_type === 'salon' ? 'services' : (restaurant.business_type === 'retail' ? 'products' : 'items') }}
              </span>
            </div>

            <div class="grid grid-cols-1 gap-3">
              <div
                v-for="item in cat.menu_items"
                :key="item.id"
                class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200/80 flex gap-3 sm:gap-4 hover:shadow-md transition"
              >
                <!-- Dish Details -->
                <div class="flex-1 flex flex-col justify-between">
                  <div>
                    <div class="flex items-center gap-1.5 mb-1 flex-wrap">
                      <!-- Salon Mode Tag vs Food Veg/Non-veg indicator -->
                      <span
                        v-if="restaurant.business_type === 'salon'"
                        class="text-[10px] font-bold text-cyan-700 bg-cyan-100 border border-cyan-300 px-1.5 py-0.2 rounded flex items-center gap-1"
                      >
                        <i class="fa-solid fa-scissors text-[9px]"></i> Service
                      </span>
                      <template v-else>
                        <span
                          v-if="item.food_type === 'veg'"
                          class="w-4 h-4 border border-emerald-600 rounded flex items-center justify-center p-0.5 shrink-0"
                          title="Vegetarian"
                        >
                          <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                        </span>
                        <span
                          v-else-if="item.food_type === 'non-veg'"
                          class="w-4 h-4 border border-rose-600 rounded flex items-center justify-center p-0.5 shrink-0"
                          title="Non-Vegetarian"
                        >
                          <span class="w-0 h-0 border-l-[4px] border-l-transparent border-r-[4px] border-r-transparent border-b-[8px] border-b-rose-600"></span>
                        </span>
                        <span
                          v-else
                          class="w-4 h-4 border border-blue-600 rounded flex items-center justify-center p-0.5 shrink-0"
                          title="Beverage"
                        >
                          <i class="fa-solid fa-glass-water text-[8px] text-blue-600"></i>
                        </span>
                      </template>

                      <h3 class="font-bold text-slate-900 text-sm sm:text-base leading-snug">
                        {{ item.name }}
                      </h3>

                      <span
                        v-if="item.is_recommended"
                        class="text-[9px] bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded"
                      >
                        {{ restaurant.business_type === 'salon' ? 'Popular Choice' : 'Bestseller' }}
                      </span>
                    </div>

                    <p class="text-xs text-slate-500 line-clamp-2 mt-1 leading-relaxed">
                      {{ item.description }}
                    </p>
                  </div>

                  <div class="flex items-center justify-between mt-3">
                    <span class="text-base font-extrabold text-slate-900">
                      {{ restaurant.currency }}{{ item.price }}
                    </span>
                    <span v-if="restaurant.business_type === 'salon'" class="text-[10px] text-cyan-700 bg-cyan-50 px-2 py-0.5 rounded-full font-semibold border border-cyan-200">
                      <i class="fa-regular fa-clock mr-0.5"></i> ~{{ item.duration_minutes || 30 }} Mins
                    </span>
                    <span v-else-if="item.prep_time_minutes" class="text-[10px] text-slate-400">
                      <i class="fa-regular fa-clock mr-0.5"></i> ~{{ item.prep_time_minutes }}m
                    </span>
                  </div>
                </div>

                <!-- Dish Image & Add Button -->
                <div class="w-24 sm:w-28 shrink-0 flex flex-col items-center justify-between">
                  <div class="w-full h-20 sm:h-24 rounded-xl overflow-hidden bg-slate-100 relative">
                    <img
                      :src="item.image_url || 'https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=300&fit=crop'"
                      :alt="item.name"
                      class="w-full h-full object-cover"
                      loading="lazy"
                    />
                    <div
                      v-if="!item.is_available"
                      class="absolute inset-0 bg-slate-950/70 backdrop-blur-xs flex items-center justify-center text-white text-[10px] font-bold"
                    >
                      Sold Out
                    </div>
                  </div>

                  <!-- Add to Cart or Quantity Increment -->
                  <div class="w-full mt-2">
                    <div v-if="!item.is_available">
                      <button disabled class="w-full py-1.5 bg-slate-200 text-slate-400 text-xs font-semibold rounded-lg cursor-not-allowed">
                        Sold Out
                      </button>
                    </div>
                    <div v-else-if="getItemQuantity(item.id) === 0">
                      <button
                        @click="addToCart(item)"
                        class="w-full py-1.5 bg-amber-500 hover:bg-amber-400 active:scale-95 text-slate-950 text-xs font-bold rounded-lg shadow-sm transition flex items-center justify-center gap-1"
                      >
                        <i class="fa-solid fa-plus text-[10px]"></i> ADD
                      </button>
                    </div>
                    <div v-else class="flex items-center justify-between bg-amber-500 text-slate-950 rounded-lg font-bold text-xs shadow-sm overflow-hidden">
                      <button @click="decrementCart(item.id)" class="px-2.5 py-1.5 hover:bg-amber-600 transition">
                        -
                      </button>
                      <span>{{ getItemQuantity(item.id) }}</span>
                      <button @click="addToCart(item)" class="px-2.5 py-1.5 hover:bg-amber-600 transition">
                        +
                      </button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Floating Bottom Cart Bar -->
      <div
        v-if="cartTotalCount > 0"
        class="fixed bottom-0 left-0 right-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200 p-3 sm:p-4 shadow-2xl"
      >
        <div class="max-w-3xl mx-auto flex items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-extrabold text-sm shadow">
              {{ cartTotalCount }}
            </div>
            <div>
              <div class="text-xs text-slate-500 font-medium">Order Total</div>
              <div class="text-lg font-extrabold text-slate-900 leading-tight">
                {{ restaurant.currency }}{{ cartSubtotal.toFixed(2) }}
              </div>
            </div>
          </div>

          <button
            @click="showCheckoutModal = true"
            class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-sm rounded-xl shadow-lg transition flex items-center gap-2 active:scale-95"
          >
            <span>View Cart & Pay</span>
            <i class="fa-solid fa-arrow-right text-xs text-amber-400"></i>
          </button>
        </div>
      </div>

      <!-- Checkout / Order Modal -->
      <div
        v-if="showCheckoutModal"
        class="fixed inset-0 z-50 bg-slate-900/70 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
      >
        <div class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-3xl max-h-[90vh] flex flex-col shadow-2xl overflow-hidden animate-slide-up">
          <!-- Modal Header -->
          <div class="p-4 border-b border-slate-200 flex items-center justify-between bg-slate-50">
            <div>
              <h3 class="font-bold text-slate-900 text-base">Checkout & Place Order</h3>
              <p class="text-xs text-slate-500">
                {{ restaurant.name }} •
                <span class="font-bold text-amber-600 uppercase">
                  {{ orderType === 'dine_in' ? currentTable : (orderType === 'delivery' ? 'Home Delivery' : 'Takeaway') }}
                </span>
              </p>
            </div>
            <button @click="showCheckoutModal = false" class="w-8 h-8 rounded-full bg-slate-200 flex items-center justify-center text-slate-600 hover:bg-slate-300">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="p-4 overflow-y-auto space-y-4 flex-1">
            <!-- Active Table Alert if Table is already occupied -->
            <div v-if="orderType === 'dine_in' && selectedTableIsOccupied" class="bg-amber-50 border border-amber-300 rounded-2xl p-3 text-xs text-amber-950">
              <div class="flex items-center gap-2 font-bold text-amber-900 mb-1">
                <i class="fa-solid fa-users text-amber-600"></i>
                <span>Table {{ currentTable }} currently has an active order!</span>
              </div>
              <p class="text-[11px] text-amber-800 leading-relaxed">
                If you are sharing this table with your group, this order will be added to <strong>{{ currentTable }}</strong>'s running bill. If you are seated at a different table, please switch your table.
              </p>
              <button
                type="button"
                @click="showChangeTableModal = true; showCheckoutModal = false"
                class="mt-2 px-3 py-1 bg-white border border-amber-300 text-amber-900 rounded-lg font-bold text-[10px] hover:bg-amber-100 transition inline-flex items-center gap-1"
              >
                <i class="fa-solid fa-chair text-amber-600"></i> Change Table
              </button>
            </div>

            <!-- Selected Items List -->
            <div>
              <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Items in Cart</h4>
              <div class="space-y-2">
                <div
                  v-for="item in cart"
                  :key="item.id"
                  class="flex items-center justify-between text-xs py-1.5 border-b border-slate-100"
                >
                  <div class="flex-1 pr-2">
                    <span class="font-bold text-slate-800">{{ item.name }}</span>
                    <div class="text-[11px] text-slate-400">{{ restaurant.currency }}{{ item.price }} x {{ item.quantity }}</div>
                  </div>
                  <div class="flex items-center gap-2">
                    <div class="flex items-center bg-slate-100 rounded-lg border border-slate-200">
                      <button @click="decrementCart(item.id)" class="px-2 py-1 text-slate-600 font-bold">-</button>
                      <span class="px-2 font-bold">{{ item.quantity }}</span>
                      <button @click="addToCart(item)" class="px-2 py-1 text-slate-600 font-bold">+</button>
                    </div>
                    <span class="font-bold text-slate-900 w-16 text-right">
                      {{ restaurant.currency }}{{ (item.price * item.quantity).toFixed(2) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Customer Details Form -->
            <div class="space-y-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
              <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                  <i class="fa-solid fa-user-pen text-amber-500"></i> Your Details
                </span>
                <span class="text-[10px] text-slate-400 font-normal">
                  {{ orderType === 'dine_in' ? 'Dine-In Guest' : (orderType === 'delivery' ? 'Home Delivery' : 'Pickup') }}
                </span>
              </h4>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 mb-1">Your Full Name *</label>
                  <input
                    v-model="customerForm.name"
                    type="text"
                    placeholder="e.g. Rahul Sharma"
                    class="w-full px-3 py-2 bg-white rounded-xl text-xs border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                  />
                </div>
                <div>
                  <label class="block text-[11px] font-semibold text-slate-600 mb-1">Phone Number (For Bill/Updates) *</label>
                  <input
                    v-model="customerForm.phone"
                    type="tel"
                    placeholder="e.g. 9876543210"
                    class="w-full px-3 py-2 bg-white rounded-xl text-xs border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                  />
                </div>
              </div>

              <!-- Home Delivery Address (Only if Delivery is selected) -->
              <div v-if="orderType === 'delivery'">
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                  Home Delivery Address *
                  <span class="text-rose-500 text-[10px] font-normal">(House/Flat, Road, Landmark)</span>
                </label>
                <textarea
                  v-model="customerForm.address"
                  rows="2"
                  placeholder="e.g. Flat 302, Sunrise Heights, Near City Mall..."
                  class="w-full px-3 py-2 bg-white rounded-xl text-xs border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                ></textarea>
              </div>

              <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Special Cooking Notes (Optional)</label>
                <input
                  v-model="customerForm.notes"
                  type="text"
                  placeholder="e.g. Less spicy, extra tissues, send disposable cutlery"
                  class="w-full px-3 py-2 bg-white rounded-xl text-xs border border-slate-300 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
                />
              </div>
            </div>

            <!-- Payment Method Selection -->
            <div>
              <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                <i class="fa-solid fa-credit-card text-amber-500"></i> Choose Payment Method
              </h4>

              <!-- Notice when Online Payment is disabled by Owner -->
              <div
                v-if="!restaurant.enable_online_payment"
                class="p-3.5 bg-slate-50 border border-slate-200 rounded-2xl flex items-center gap-3"
              >
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg shrink-0">
                  <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <div>
                  <div class="text-xs font-bold text-slate-900">Direct Pay at Counter</div>
                  <div class="text-[11px] text-slate-500">
                    {{ orderType === 'delivery' ? 'Pay upon delivery via cash or UPI.' : 'Your order will be sent to the kitchen or service desk. Settle the bill via cash or card at the counter or table.' }}
                  </div>
                </div>
              </div>

              <!-- Options Grid if Online Payment is enabled -->
              <div v-else class="grid grid-cols-2 gap-3">
                <label
                  :class="[
                    'p-3 rounded-2xl border cursor-pointer transition flex flex-col justify-between',
                    paymentMethod === 'upi' ? 'bg-amber-50 border-amber-500 ring-2 ring-amber-500/20' : 'bg-white border-slate-200'
                  ]"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                      <i class="fa-solid fa-mobile-screen-button text-amber-600"></i> UPI / Online
                    </span>
                    <input type="radio" value="upi" v-model="paymentMethod" class="accent-amber-500" />
                  </div>
                  <span class="text-[10px] text-slate-500">GPay, PhonePe, Paytm, QR Scan</span>
                </label>

                <label
                  v-if="restaurant.enable_cash_payment !== false"
                  :class="[
                    'p-3 rounded-2xl border cursor-pointer transition flex flex-col justify-between',
                    paymentMethod === 'cash' ? 'bg-amber-50 border-amber-500 ring-2 ring-amber-500/20' : 'bg-white border-slate-200'
                  ]"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold text-slate-900 flex items-center gap-1.5">
                      <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                      {{ orderType === 'delivery' ? 'Cash on Delivery' : 'Pay at Counter' }}
                    </span>
                    <input type="radio" value="cash" v-model="paymentMethod" class="accent-amber-500" />
                  </div>
                  <span class="text-[10px] text-slate-500">
                    {{ orderType === 'delivery' ? 'Pay upon delivery at home' : 'Cash or Card after food is served' }}
                  </span>
                </label>
              </div>
            </div>

            <!-- Pricing Calculation Breakdown -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
              <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span>{{ restaurant.currency }}{{ cartSubtotal.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span>GST / Taxes ({{ restaurant.tax_percentage }}%):</span>
                <span>{{ restaurant.currency }}{{ taxAmount.toFixed(2) }}</span>
              </div>
              <div class="flex justify-between text-slate-900 font-extrabold text-sm pt-2 border-t border-slate-200">
                <span>Total Payable:</span>
                <span class="text-amber-600">{{ restaurant.currency }}{{ grandTotal.toFixed(2) }}</span>
              </div>
            </div>

            <div v-if="orderError" class="p-3 bg-rose-50 border border-rose-200 text-rose-600 text-xs rounded-xl">
              <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ orderError }}
            </div>
          </div>

          <!-- Modal Footer / Place Order Button -->
          <div class="p-4 border-t border-slate-200 bg-white">
            <button
              @click="submitOrder"
              :disabled="submittingOrder"
              class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50"
            >
              <i v-if="submittingOrder" class="fa-solid fa-circle-notch fa-spin"></i>
              <span v-else>Confirm & Pay {{ restaurant.currency }}{{ grandTotal.toFixed(2) }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- Change Table Modal (Shows Dynamic Tables & Occupancy Status) -->
      <div
        v-if="showChangeTableModal"
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4"
      >
        <div class="bg-white w-full max-w-md rounded-3xl p-5 sm:p-6 shadow-2xl">
          <div class="flex items-center justify-between mb-2">
            <h3 class="font-bold text-slate-900 text-base">Select Your Dining Table</h3>
            <button @click="showChangeTableModal = false" class="text-slate-400 hover:text-slate-700">
              <i class="fa-solid fa-xmark"></i>
            </button>
          </div>
          <p class="text-xs text-slate-500 mb-4">Choose the table where you are seated</p>

          <!-- Status Legend -->
          <div class="flex items-center gap-4 text-[11px] mb-3 p-2 bg-slate-50 rounded-xl border border-slate-200">
            <span class="flex items-center gap-1.5 font-semibold text-emerald-700">
              <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Available (Free)
            </span>
            <span class="flex items-center gap-1.5 font-semibold text-amber-700">
              <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Occupied (Active Diners)
            </span>
          </div>

          <!-- Dynamic Tables Grid -->
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-72 overflow-y-auto mb-4 p-1">
            <button
              v-for="tbl in dynamicTables"
              :key="tbl.id"
              @click="selectTable(tbl)"
              :class="[
                'p-3 rounded-2xl text-left transition border flex flex-col justify-between relative',
                currentTable === tbl.name
                  ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/30'
                  : 'border-slate-200 bg-white hover:border-slate-300'
              ]"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="font-extrabold text-xs text-slate-900">{{ tbl.name }}</span>
                <span
                  :class="[
                    'w-2 h-2 rounded-full',
                    tbl.status === 'occupied' ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500'
                  ]"
                  :title="tbl.status === 'occupied' ? 'Occupied' : 'Available'"
                ></span>
              </div>

              <div class="flex items-center justify-between text-[10px] text-slate-400">
                <span>{{ tbl.seating_capacity }} Seater</span>
                <span :class="tbl.status === 'occupied' ? 'text-rose-600 font-bold' : 'text-emerald-600 font-bold'">
                  {{ tbl.status === 'occupied' ? 'Busy' : 'Free' }}
                </span>
              </div>
            </button>
          </div>

          <!-- Occupied Table Alert if selected table is occupied -->
          <div v-if="selectedTableIsOccupied" class="p-3 mb-3 bg-amber-50 border border-amber-300 rounded-xl text-[11px] text-amber-900 flex items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5"></i>
            <div>
              <span class="font-bold">{{ currentTable }} already has an active order.</span>
              <p class="text-[10px] text-amber-800 mt-0.5">If you are seated at this table and adding more items, proceed ahead. Otherwise, select any vacant table.</p>
            </div>
          </div>

          <button
            @click="showChangeTableModal = false"
            class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition"
          >
            Confirm {{ currentTable }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal: Select Appointment Date & Time Slot (Barbershop / Salon) -->
    <div
      v-if="showSlotModal"
      class="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
      @click.self="showSlotModal = false"
    >
      <div class="bg-white rounded-3xl max-w-md w-full shadow-2xl border border-slate-200 overflow-hidden flex flex-col">
        <!-- Header -->
        <div class="p-4 bg-slate-900 text-white flex items-center justify-between border-b border-slate-800">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock text-cyan-400"></i>
            <div>
              <h3 class="font-extrabold text-sm text-white">Select Appointment Time Slot</h3>
              <p class="text-[10px] text-slate-400">Skip the waiting line • Real-time slot booking</p>
            </div>
          </div>
          <button @click="showSlotModal = false" class="w-7 h-7 rounded-full bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center cursor-pointer">
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>

        <div class="p-5 space-y-4 max-h-[80vh] overflow-y-auto text-xs">
          <!-- Step 1: Choose Date -->
          <div>
            <label class="block text-xs font-bold text-slate-800 mb-2">1. Choose Booking Date</label>
            <div class="flex items-center gap-2">
              <button
                type="button"
                @click="changeBookingDate(todayDate)"
                :class="[
                  'flex-1 py-2 px-3 rounded-xl font-bold text-xs border transition cursor-pointer',
                  selectedBookingDate === todayDate ? 'bg-cyan-500 text-slate-950 border-cyan-500 shadow' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200'
                ]"
              >
                Today ({{ formatSlotDate(todayDate) }})
              </button>

              <button
                type="button"
                @click="changeBookingDate(tomorrowDate)"
                :class="[
                  'flex-1 py-2 px-3 rounded-xl font-bold text-xs border transition cursor-pointer',
                  selectedBookingDate === tomorrowDate ? 'bg-cyan-500 text-slate-950 border-cyan-500 shadow' : 'bg-slate-100 text-slate-700 border-slate-200 hover:bg-slate-200'
                ]"
              >
                Tomorrow ({{ formatSlotDate(tomorrowDate) }})
              </button>
            </div>
          </div>

          <!-- Step 2: Choose Stylist / Chair (Optional) -->
          <div v-if="salonChairs.length > 0">
            <label class="block text-xs font-bold text-slate-800 mb-1.5">2. Preferred Chair / Stylist (Optional)</label>
            <select
              v-model="selectedChair"
              class="w-full px-3 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs text-slate-900 focus:outline-none"
            >
              <option value="">Any Available Chair / Stylist</option>
              <option v-for="ch in salonChairs" :key="ch.id" :value="ch.name">
                {{ ch.name }}
              </option>
            </select>
          </div>

          <!-- Step 3: Choose Time Slot -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="text-xs font-bold text-slate-800">3. Select Time Slot ({{ salonSlotDuration }} Mins)</label>
              <span v-if="loadingSlots" class="text-[10px] text-cyan-600 font-bold flex items-center gap-1">
                <i class="fa-solid fa-circle-notch fa-spin"></i> Checking slots...
              </span>
            </div>

            <!-- Slots Grid -->
            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 max-h-56 overflow-y-auto p-1">
              <button
                v-for="s in salonSlots"
                :key="s.time"
                :disabled="!s.is_available"
                @click="selectSlot(s)"
                :class="[
                  'p-2.5 rounded-xl border text-center transition flex flex-col items-center justify-center gap-0.5 cursor-pointer',
                  selectedBookingTime === s.time
                    ? 'bg-cyan-500 text-slate-950 border-cyan-500 font-extrabold shadow ring-2 ring-cyan-500/30'
                    : (s.is_available
                        ? 'bg-cyan-50/60 border-cyan-200 text-slate-900 hover:bg-cyan-100/60 hover:border-cyan-400'
                        : 'bg-slate-100 border-slate-200 text-slate-400 cursor-not-allowed opacity-60')
                ]"
              >
                <span class="font-bold text-xs">{{ s.display_time }}</span>
                <span
                  :class="[
                    'text-[9px] font-semibold',
                    selectedBookingTime === s.time ? 'text-slate-950' : (s.is_available ? 'text-cyan-700' : 'text-slate-400')
                  ]"
                >
                  {{ s.is_available ? 'Available' : (s.is_past ? 'Passed' : 'Booked') }}
                </span>
              </button>
            </div>
          </div>

          <button
            @click="showSlotModal = false"
            class="w-full py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-xl transition text-xs shadow-lg mt-2 cursor-pointer"
          >
            Confirm Slot ({{ selectedBookingTimeDisplay || 'Immediate' }})
          </button>
        </div>
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
              <h3 class="font-bold text-xs sm:text-sm leading-tight">{{ restaurant.name }} - Digital Menu Card</h3>
              <p class="text-[10px] text-slate-400">Print ready PDF & paper menu</p>
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
                  v-if="restaurant.logo_url"
                  :src="restaurant.logo_url"
                  class="w-12 h-12 rounded-xl object-cover border border-slate-200 shadow-sm"
                />
                <div class="text-left">
                  <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-tight">
                    {{ restaurant.name }}
                  </h1>
                  <p v-if="restaurant.tagline" class="text-xs font-semibold text-amber-600">
                    {{ restaurant.tagline }}
                  </p>
                </div>
              </div>

              <p v-if="restaurant.description" class="text-xs text-slate-500 max-w-md mx-auto mt-1">
                {{ restaurant.description }}
              </p>

              <div class="text-[11px] text-slate-500 mt-2 flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <span v-if="restaurant.phone"><i class="fa-solid fa-phone text-slate-400 mr-1"></i> {{ restaurant.phone }}</span>
                <span v-if="restaurant.email"><i class="fa-solid fa-envelope text-slate-400 mr-1"></i> {{ restaurant.email }}</span>
                <span v-if="restaurant.address"><i class="fa-solid fa-location-dot text-slate-400 mr-1"></i> {{ restaurant.address }}</span>
              </div>

              <div class="inline-block mt-3 px-4 py-0.5 bg-amber-500 text-slate-950 font-extrabold text-[11px] tracking-widest uppercase rounded-full">
                ✦ RESTAURANT MENU ✦
              </div>
            </div>

            <!-- Menu Categories & Items -->
            <div class="space-y-5">
              <div v-for="cat in (restaurant.categories || [])" :key="cat.id" class="space-y-2.5">
                <!-- Category Title Header -->
                <div class="flex items-center gap-2 border-b border-slate-200 pb-1">
                  <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                  <h2 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider">{{ cat.name }}</h2>
                  <span class="text-[10px] text-slate-400 font-normal">({{ cat.menu_items?.length || 0 }})</span>
                  <div class="flex-1 border-b border-dotted border-slate-300 ml-2"></div>
                </div>

                <!-- Dishes List -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                  <div
                    v-for="item in (cat.menu_items || [])"
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
                          {{ restaurant.currency }}{{ item.price }}
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
                  * Taxes ({{ restaurant.tax_percentage }}% GST) applicable as per menu policy.
                </div>
                <div class="text-[9px] text-slate-400 font-mono truncate max-w-xs">
                  {{ windowOrigin }}/r/{{ restaurant.slug }}
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
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import QRCode from 'qrcode';
import html2pdf from 'html2pdf.js';

const route = useRoute();
const router = useRouter();

const restaurant = ref(null);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategory = ref('all');
const vegOnly = ref(false);

const orderType = ref('dine_in'); // 'dine_in', 'delivery', 'takeaway'
const currentTable = ref('Table 1');
const showChangeTableModal = ref(false);
const showCheckoutModal = ref(false);

// Cart: [{ id, name, price, quantity }]
const cart = ref([]);

const customerForm = ref({
  name: '',
  phone: '',
  address: '',
  notes: '',
});
const paymentMethod = ref('upi');
const submittingOrder = ref(false);
const orderError = ref('');

// Salon Booking & Slots state
const salonSlots = ref([]);
const salonChairs = ref([]);
const salonQueue = ref({ now_serving_token: 1, total_booked_today: 0 });
const salonSlotDuration = ref(30);
const loadingSlots = ref(false);
const showSlotModal = ref(false);

const getTodayDateStr = () => {
  const now = new Date();
  const y = now.getFullYear();
  const m = String(now.getMonth() + 1).padStart(2, '0');
  const d = String(now.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
};

const getTomorrowDateStr = () => {
  const tomorrow = new Date();
  tomorrow.setDate(tomorrow.getDate() + 1);
  const y = tomorrow.getFullYear();
  const m = String(tomorrow.getMonth() + 1).padStart(2, '0');
  const d = String(tomorrow.getDate()).padStart(2, '0');
  return `${y}-${m}-${d}`;
};

const todayDate = ref(getTodayDateStr());
const tomorrowDate = ref(getTomorrowDateStr());
const selectedBookingDate = ref(todayDate.value);
const selectedBookingTime = ref('');
const selectedBookingTimeDisplay = ref('');
const selectedChair = ref('');

const formatSlotDate = (dateStr) => {
  if (!dateStr) return '';
  const d = new Date(dateStr);
  return d.toLocaleDateString([], { month: 'short', day: 'numeric' });
};

const fetchSlots = async (date) => {
  if (!restaurant.value) return;
  loadingSlots.value = true;
  try {
    const res = await axios.get(`/r/${restaurant.value.slug}/slots?date=${date}`);
    if (res.data.success) {
      salonSlots.value = res.data.slots || [];
      salonChairs.value = res.data.chairs || [];
      salonQueue.value = res.data.queue || { now_serving_token: 1, total_booked_today: 0 };
      salonSlotDuration.value = res.data.slot_duration_minutes || 30;

      if (!selectedBookingTime.value) {
        const firstAvailable = salonSlots.value.find(s => s.is_available);
        if (firstAvailable) {
          selectedBookingTime.value = firstAvailable.time;
          selectedBookingTimeDisplay.value = firstAvailable.display_time;
        }
      }
    }
  } catch (err) {
    console.error('Failed to load slots', err);
  } finally {
    loadingSlots.value = false;
  }
};

const changeBookingDate = (date) => {
  selectedBookingDate.value = date;
  selectedBookingTime.value = '';
  selectedBookingTimeDisplay.value = '';
  fetchSlots(date);
};

const selectSlot = (slot) => {
  selectedBookingTime.value = slot.time;
  selectedBookingTimeDisplay.value = slot.display_time;
};

const openSlotModal = () => {
  showSlotModal.value = true;
  fetchSlots(selectedBookingDate.value);
};

// Menu PDF Modal & Generator state
const showPdfModal = ref(false);
const generatingPdf = ref(false);
const menuPdfQrCode = ref('');
const windowOrigin = computed(() => typeof window !== 'undefined' ? window.location.origin : '');

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
      filename: `${restaurant.value.slug || 'restaurant'}-menu.pdf`,
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

const fetchMenu = async () => {
  try {
    const slug = route.params.slug;
    const res = await axios.get(`/r/${slug}`);
    if (res.data.success) {
      restaurant.value = res.data.restaurant;

      // If user opened with table query parameter (e.g. ?table=3 or ?table=VIP-1)
      if (route.query.table) {
        orderType.value = 'dine_in';
        currentTable.value = isNaN(route.query.table)
          ? route.query.table
          : `Table ${route.query.table}`;
      } else if (restaurant.value.tables && restaurant.value.tables.length > 0) {
        // Set first available table if possible
        const freeTable = restaurant.value.tables.find(t => t.status === 'available');
        currentTable.value = freeTable ? freeTable.name : restaurant.value.tables[0].name;
      }

      // Default payment method according to restaurant policy
      if (!restaurant.value.enable_online_payment) {
        paymentMethod.value = 'cash';
      } else if (restaurant.value.enable_cash_payment === false) {
        paymentMethod.value = 'upi';
      }

      // If salon, load live slots & queue
      if (restaurant.value.business_type === 'salon') {
        orderType.value = 'dine_in';
        fetchSlots(selectedBookingDate.value);
      }
    }
  } catch (err) {
    console.error('Failed to load menu', err);
  } finally {
    loading.value = false;
  }
};

const setOrderType = (type) => {
  orderType.value = type;
  if (type === 'delivery' || type === 'takeaway') {
    // No table needed for home delivery or takeaway
  }
};

const dynamicTables = computed(() => {
  if (!restaurant.value || !restaurant.value.tables || restaurant.value.tables.length === 0) {
    // Fallback if no table records yet
    const count = restaurant.value?.table_count || 10;
    return Array.from({ length: count }, (_, i) => ({
      id: i + 1,
      name: `Table ${i + 1}`,
      code: `T${i + 1}`,
      seating_capacity: 4,
      status: 'available',
    }));
  }
  return restaurant.value.tables;
});

const selectedTableIsOccupied = computed(() => {
  if (!restaurant.value?.tables) return false;
  const match = restaurant.value.tables.find(t => t.name === currentTable.value);
  return match && match.status === 'occupied';
});

const toggleVegOnly = () => {
  vegOnly.value = !vegOnly.value;
};

const resetFilters = () => {
  searchQuery.value = '';
  selectedCategory.value = 'all';
  vegOnly.value = false;
};

const selectTable = (tableObj) => {
  currentTable.value = tableObj.name;
};

// Filtered categories & items
const filteredCategories = computed(() => {
  if (!restaurant.value || !restaurant.value.categories) return [];

  return restaurant.value.categories
    .filter(cat => {
      if (selectedCategory.value !== 'all' && cat.id !== selectedCategory.value) {
        return false;
      }
      return true;
    })
    .map(cat => {
      const items = (cat.menu_items || []).filter(item => {
        if (vegOnly.value && item.food_type !== 'veg') {
          return false;
        }
        if (searchQuery.value) {
          const q = searchQuery.value.toLowerCase();
          const nameMatch = item.name.toLowerCase().includes(q);
          const descMatch = (item.description || '').toLowerCase().includes(q);
          if (!nameMatch && !descMatch) return false;
        }
        return true;
      });

      return {
        ...cat,
        menu_items: items,
      };
    })
    .filter(cat => cat.menu_items.length > 0);
});

// Cart helper methods
const getItemQuantity = (itemId) => {
  const item = cart.value.find(i => i.id === itemId);
  return item ? item.quantity : 0;
};

const addToCart = (item) => {
  const existing = cart.value.find(i => i.id === item.id);
  if (existing) {
    existing.quantity++;
  } else {
    cart.value.push({
      id: item.id,
      name: item.name,
      price: item.price,
      quantity: 1,
    });
  }
};

const decrementCart = (itemId) => {
  const index = cart.value.findIndex(i => i.id === itemId);
  if (index !== -1) {
    if (cart.value[index].quantity > 1) {
      cart.value[index].quantity--;
    } else {
      cart.value.splice(index, 1);
    }
  }
};

const cartTotalCount = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

const cartSubtotal = computed(() => {
  return cart.value.reduce((sum, item) => sum + item.price * item.quantity, 0);
});

const taxAmount = computed(() => {
  const taxPercent = restaurant.value?.tax_percentage || 0;
  return (cartSubtotal.value * taxPercent) / 100;
});

const grandTotal = computed(() => {
  return cartSubtotal.value + taxAmount.value;
});

// Submit Order
const submitOrder = async () => {
  if (!customerForm.value.name.trim()) {
    orderError.value = 'Please enter your name.';
    return;
  }
  if (!customerForm.value.phone.trim()) {
    orderError.value = 'Please enter your phone number.';
    return;
  }
  if (orderType.value === 'delivery' && !customerForm.value.address.trim()) {
    orderError.value = 'Please enter your home delivery address.';
    return;
  }

  orderError.value = '';
  submittingOrder.value = true;

  try {
    const isSalon = restaurant.value.business_type === 'salon';
    const payload = {
      restaurant_id: restaurant.value.id,
      order_type: isSalon ? 'dine_in' : orderType.value,
      table_number: isSalon
        ? (selectedChair.value || currentTable.value || 'Chair 1')
        : (orderType.value === 'dine_in' ? currentTable.value : null),
      booking_date: isSalon ? selectedBookingDate.value : null,
      booking_time: isSalon ? selectedBookingTime.value : null,
      chair_or_specialist: isSalon ? (selectedChair.value || currentTable.value || 'Chair 1') : null,
      delivery_address: orderType.value === 'delivery' ? customerForm.value.address : null,
      customer_name: customerForm.value.name,
      customer_phone: customerForm.value.phone,
      special_notes: customerForm.value.notes,
      payment_method: paymentMethod.value,
      items: cart.value.map(i => ({ id: i.id, quantity: i.quantity })),
    };

    const res = await axios.post('/orders', payload);

    if (res.data.success) {
      cart.value = [];
      showCheckoutModal.value = false;
      // Redirect to Order tracking / receipt page
      router.push(`/order-track/${res.data.order.order_code}`);
    }
  } catch (err) {
    orderError.value = err.response?.data?.message || 'Failed to place order. Please try again.';
  } finally {
    submittingOrder.value = false;
  }
};

onMounted(() => {
  fetchMenu();
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
  #printable-menu-card, #printable-menu-card * {
    visibility: visible;
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
