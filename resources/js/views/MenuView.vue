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
      <!-- 5 KM Geofence Nearby Reward Toast / Notification -->
      <transition enter-active-class="transform transition ease-out duration-300" enter-from-class="-translate-y-full opacity-0" enter-to-class="translate-y-0 opacity-100" leave-active-class="transition ease-in duration-200" leave-from-class="opacity-100" leave-to-class="-translate-y-full opacity-0">
        <div
          v-if="showNearbyAlert && isWithinGeofence"
          class="fixed top-3 left-1/2 -translate-x-1/2 z-50 w-11/12 max-w-md bg-gradient-to-r from-amber-600 via-orange-600 to-purple-700 text-white p-3.5 rounded-2xl shadow-2xl border-2 border-white/50 flex items-center justify-between gap-3"
        >
          <div class="flex items-center gap-2.5">
            <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-xl shrink-0">
              📍
            </div>
            <div>
              <div class="flex items-center gap-1.5 flex-wrap">
                <span class="font-black text-xs">You're Nearby! ({{ userDistanceKm !== null ? userDistanceKm.toFixed(1) : '<5' }} km)</span>
                <span class="bg-amber-300 text-slate-950 font-black text-[9px] px-1.5 rounded-full uppercase">5 KM REWARD</span>
              </div>
              <p class="text-[11px] text-white/95 mt-0.5">
                You are in our Royalty Rewards zone! Spin the Lucky Wheel for up to 40% OFF today!
              </p>
            </div>
          </div>
          <div class="flex items-center gap-1.5 shrink-0">
            <button
              @click="openLoyaltyModal(); showNearbyAlert = false;"
              class="px-3 py-1.5 bg-white hover:bg-slate-100 text-slate-950 font-black text-[11px] rounded-xl shadow cursor-pointer"
            >
              Spin Now
            </button>
            <button
              @click="showNearbyAlert = false"
              class="w-6 h-6 rounded-full bg-black/20 hover:bg-black/30 flex items-center justify-center text-white/80 cursor-pointer"
            >
              <i class="fa-solid fa-xmark text-xs"></i>
            </button>
          </div>
        </div>
      </transition>

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
                v-if="restaurant.enable_home_delivery !== false"
                @click="setOrderType('delivery')"
                :class="[
                  'px-3 py-1.5 rounded-lg font-bold text-xs transition flex items-center gap-1.5',
                  orderType === 'delivery' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-300 hover:text-white'
                ]"
              >
                <i class="fa-solid fa-motorcycle text-[11px]"></i> Home Delivery
              </button>

              <button
                v-if="restaurant.enable_takeaway !== false"
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
              <div
                v-if="orderType === 'dine_in'"
                class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border font-semibold text-xs transition"
                :class="selectedTableIsOccupied ? 'bg-rose-500/20 text-rose-300 border-rose-500/40' : 'bg-amber-500/20 text-amber-300 border-amber-500/40'"
              >
                <i class="fa-solid fa-chair text-xs"></i>
                <span>{{ currentTable || 'Select Table' }}</span>
                <span v-if="selectedTableIsOccupied" class="text-[10px] bg-rose-500 text-white px-1.5 py-0.2 rounded-full font-bold ml-1">Occupied</span>
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

      <!-- Loyalty & Rewards Banner Card -->
      <div v-if="loyaltyStatus && loyaltyStatus.loyalty_enabled !== false" class="max-w-3xl mx-auto px-4 mt-3 mb-1">
        <div
          @click="openLoyaltyModal"
          class="bg-gradient-to-r from-amber-500/10 via-orange-500/15 to-purple-500/15 hover:from-amber-500/20 hover:to-purple-500/20 border-2 border-amber-500/40 rounded-2xl p-3 sm:p-4 shadow-sm transition cursor-pointer flex items-center justify-between gap-3 group"
        >
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 flex items-center justify-center text-lg font-black shadow shrink-0 group-hover:scale-105 transition">
              <i class="fa-solid fa-crown animate-pulse"></i>
            </div>
            <div>
              <div class="flex items-center gap-2 flex-wrap">
                <span class="font-extrabold text-xs sm:text-sm text-slate-900">
                  Royalty Rewards Club
                </span>
                <span
                  v-if="loyaltyStatus.customer && loyaltyStatus.customer.can_play"
                  class="bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider animate-bounce shadow"
                >
                  🎉 Ticket Ready to Spin!
                </span>
                <span
                  v-else-if="loyaltyStatus.customer && loyaltyStatus.customer.visit_count > 0"
                  class="bg-amber-100 text-amber-900 border border-amber-300 font-bold text-[10px] px-2 py-0.5 rounded-full"
                >
                  {{ loyaltyStatus.customer.visit_count }} / {{ loyaltyStatus.required_visits }} Visits
                </span>
              </div>
              <p class="text-[11px] text-slate-600 mt-0.5">
                <template v-if="loyaltyStatus.customer && loyaltyStatus.customer.can_play">
                  Aapke paas <strong>1 Lucky Reward Ticket</strong> ready hai! Spin karein aur discount jeetein!
                </template>
                <template v-else-if="loyaltyStatus.customer && loyaltyStatus.customer.visit_count > 0">
                  {{ loyaltyStatus.customer.visits_remaining }} aur visit par Lucky Spinner / Scratch Card unlock hoga!
                </template>
                <template v-else>
                  Har visit par 1 stamp payein. {{ loyaltyStatus.required_visits || 5 }} visits par Lucky Spin se jeetein offers!
                </template>
              </p>

              <!-- Nearby Location Status / Distance Pill -->
              <div v-if="userDistanceKm !== null" class="flex items-center gap-1 text-[10px] mt-1.5 font-bold">
                <span
                  :class="[
                    'px-2 py-0.5 rounded-full flex items-center gap-1',
                    isWithinGeofence
                      ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                      : 'bg-slate-100 text-slate-600 border border-slate-200'
                  ]"
                >
                  <i class="fa-solid fa-location-dot" :class="isWithinGeofence ? 'text-emerald-600' : 'text-slate-400'"></i>
                  <span v-if="isWithinGeofence">Within 5 km Reward Zone ({{ userDistanceKm.toFixed(1) }} km away)</span>
                  <span v-else>{{ userDistanceKm.toFixed(1) }} km away from restaurant</span>
                </span>
              </div>
              <div v-else-if="!locationChecked" class="mt-1">
                <button
                  type="button"
                  @click.stop="requestUserLocation"
                  class="text-[10px] text-amber-700 hover:text-amber-900 font-bold underline flex items-center gap-1 cursor-pointer"
                >
                  <i class="fa-solid fa-location-crosshairs text-amber-600"></i>
                  <span>Turn ON Location for 5 km Nearby Reward Alerts</span>
                </button>
              </div>
            </div>
          </div>

          <div class="shrink-0 flex items-center gap-2">
            <button
              v-if="loyaltyStatus.customer && loyaltyStatus.customer.can_play"
              class="px-3.5 py-1.5 bg-gradient-to-r from-purple-600 via-pink-600 to-amber-500 text-white font-extrabold text-xs rounded-xl shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
              <i class="fa-solid fa-dharmachakra animate-spin"></i>
              <span>Spin & Win</span>
            </button>
            <button
              v-else
              class="px-3 py-1.5 bg-white text-slate-800 border border-slate-300 font-bold text-xs rounded-xl shadow-xs transition flex items-center gap-1 cursor-pointer"
            >
              <i class="fa-solid fa-ticket text-amber-500"></i>
              <span class="hidden sm:inline">View</span> Stamps
            </button>
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
                :placeholder="currentLang === 'hi' ? 'डिश या ड्रिंक खोजें...' : (restaurant.business_type === 'salon' ? 'Search haircut, styling, beard, facial, spa...' : (restaurant.business_type === 'retail' ? 'Search shirts, jeans, footwear...' : 'Search dishes, drinks, starters...'))"
                class="w-full pl-8 pr-16 py-2 bg-slate-100 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-amber-500/30 border border-slate-200"
              />
              <div class="absolute right-2.5 top-1/2 -translate-y-1/2 flex items-center gap-1.5">
                <button
                  v-if="searchQuery"
                  @click="searchQuery = ''"
                  class="text-slate-400 hover:text-slate-600 text-xs"
                >
                  <i class="fa-solid fa-xmark"></i>
                </button>
                <!-- Voice Search Mic Button -->
                <button
                  v-if="restaurant?.enable_voice_search"
                  @click="toggleVoiceSearch"
                  type="button"
                  :class="[
                    'p-1.5 rounded-lg text-xs transition flex items-center justify-center cursor-pointer',
                    isListening ? 'bg-rose-500 text-white animate-pulse shadow-md shadow-rose-500/30' : 'text-slate-400 hover:text-amber-600'
                  ]"
                  :title="isListening ? 'Listening... Speak dish name' : 'Voice Search (Speak Dish Name)'"
                >
                  <i :class="isListening ? 'fa-solid fa-microphone-lines text-white' : 'fa-solid fa-microphone'"></i>
                </button>
              </div>
            </div>

            <!-- Language Switcher Toggle -->
            <button
              v-if="restaurant?.enable_voice_search"
              @click="toggleLanguage"
              type="button"
              class="px-2.5 py-2 rounded-xl text-xs font-black transition border shrink-0 bg-slate-100 hover:bg-slate-200 text-slate-700 border-slate-200 flex items-center gap-1 cursor-pointer"
              title="Toggle Language (English / हिन्दी)"
            >
              <i class="fa-solid fa-language text-amber-500"></i>
              <span>{{ currentLang === 'hi' ? 'हिन्दी' : 'ENG' }}</span>
            </button>

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
            <!-- If user is seated at this table and adding more items (Running Order) -->
            <div v-if="orderType === 'dine_in' && selectedTableIsOccupied && isMyActiveTable" class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-3 text-xs text-amber-950 flex items-start gap-2.5">
              <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-700 flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-bell-concierge text-sm"></i>
              </div>
              <div>
                <div class="font-extrabold text-amber-900">Adding to Your Table Order ({{ currentTable }})</div>
                <p class="text-[11px] text-amber-800 mt-0.5 leading-tight">These extra items will be sent to the kitchen and added to your ongoing meal bill.</p>
              </div>
            </div>

            <!-- If table is occupied by someone else (Blocked) -->
            <div v-else-if="orderType === 'dine_in' && selectedTableIsOccupied && !isMyActiveTable" class="bg-rose-50 border border-rose-300 rounded-2xl p-3.5 text-xs text-rose-950">
              <div class="flex items-center gap-2 font-bold text-rose-900 mb-1">
                <i class="fa-solid fa-triangle-exclamation text-rose-600"></i>
                <span>{{ currentTable }} is currently occupied with active diners!</span>
              </div>
              <p class="text-[11px] text-rose-800 leading-relaxed">
                This table cannot accept a new order right now. Please select an available free table or ask hotel staff to allocate a table.
              </p>
              <button
                type="button"
                @click="showChangeTableModal = true; showCheckoutModal = false"
                class="mt-2.5 px-3.5 py-1.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl font-bold text-xs shadow inline-flex items-center gap-1.5 cursor-pointer"
              >
                <i class="fa-solid fa-chair"></i> Select an Available Table
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

            <!-- Royalty Reward Voucher Applied Banner -->
            <div v-if="activeLoyaltyVoucher" class="bg-gradient-to-r from-amber-500/15 via-orange-500/15 to-purple-500/15 border-2 border-amber-400 rounded-2xl p-3 flex items-center justify-between gap-2 shadow-xs">
              <div class="flex items-center gap-2">
                <span class="text-xl">👑</span>
                <div>
                  <div class="text-[11px] font-black text-amber-950 flex items-center gap-1.5">
                    <span>Royalty Reward Applied!</span>
                    <span class="bg-amber-500 text-slate-950 font-black text-[9px] px-1.5 py-0.2 rounded-md uppercase tracking-wide">VOUCHER</span>
                  </div>
                  <div class="text-[10px] text-slate-700 font-semibold">{{ activeLoyaltyVoucher.reward_title || activeLoyaltyVoucher.title }}</div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <span class="text-xs font-black text-emerald-600">-{{ restaurant.currency }}{{ loyaltyDiscountAmount.toFixed(2) }}</span>
                <button
                  type="button"
                  @click="removeVoucher"
                  class="text-[10px] text-rose-500 hover:text-rose-700 underline font-semibold cursor-pointer"
                  title="Remove voucher"
                >
                  Remove
                </button>
              </div>
            </div>

            <!-- Pricing Calculation Breakdown -->
            <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-1.5 text-xs">
              <div class="flex justify-between text-slate-600">
                <span>Subtotal:</span>
                <span>{{ restaurant.currency }}{{ cartSubtotal.toFixed(2) }}</span>
              </div>
              <div v-if="activeLoyaltyVoucher" class="flex justify-between text-emerald-600 font-bold">
                <span class="flex items-center gap-1">
                  <i class="fa-solid fa-crown text-amber-500"></i> Royalty Discount ({{ activeLoyaltyVoucher.reward_title || activeLoyaltyVoucher.title }}):
                </span>
                <span>-{{ restaurant.currency }}{{ loyaltyDiscountAmount.toFixed(2) }}</span>
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
              :disabled="submittingOrder || isTableBlockedForOrder"
              class="w-full py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-extrabold text-sm rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <i v-if="submittingOrder" class="fa-solid fa-circle-notch fa-spin"></i>
              <span v-else-if="isTableBlockedForOrder">
                Table Occupied — Select Available Table
              </span>
              <span v-else-if="orderType === 'dine_in' && selectedTableIsOccupied && isMyActiveTable">
                Add to {{ currentTable }} Order • {{ restaurant.currency }}{{ grandTotal.toFixed(2) }}
              </span>
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
              :disabled="tbl.status === 'occupied' && !(tbl.name === currentTable && isMyActiveTable)"
              :class="[
                'p-3 rounded-2xl text-left transition border flex flex-col justify-between relative',
                tbl.status === 'occupied' && !(tbl.name === currentTable && isMyActiveTable)
                  ? 'border-rose-200 bg-rose-50/70 opacity-70 cursor-not-allowed'
                  : currentTable === tbl.name
                    ? 'border-amber-500 bg-amber-50/70 ring-2 ring-amber-500/30 cursor-pointer shadow-sm'
                    : 'border-slate-200 bg-white hover:border-slate-300 cursor-pointer'
              ]"
            >
              <div class="flex items-center justify-between mb-1">
                <span class="font-extrabold text-xs" :class="tbl.status === 'occupied' && !(tbl.name === currentTable && isMyActiveTable) ? 'text-slate-500' : 'text-slate-900'">{{ tbl.name }}</span>
                <span
                  :class="[
                    'w-2 h-2 rounded-full',
                    tbl.status === 'occupied'
                      ? (tbl.name === currentTable && isMyActiveTable ? 'bg-amber-500' : 'bg-rose-500 animate-pulse')
                      : 'bg-emerald-500'
                  ]"
                  :title="tbl.status === 'occupied' ? 'Occupied' : 'Available'"
                ></span>
              </div>

              <div class="flex items-center justify-between text-[10px]">
                <span class="text-slate-400">{{ tbl.seating_capacity }} Seater</span>
                <span
                  :class="[
                    'font-bold',
                    tbl.status === 'occupied'
                      ? (tbl.name === currentTable && isMyActiveTable ? 'text-amber-600' : 'text-rose-600')
                      : 'text-emerald-600'
                  ]"
                >
                  {{ tbl.status === 'occupied' ? (tbl.name === currentTable && isMyActiveTable ? 'Your Table' : 'Occupied') : 'Available' }}
                </span>
              </div>
            </button>
          </div>

          <!-- Occupied Table Alert if selected table is occupied by someone else -->
          <div v-if="selectedTableIsOccupied && !isMyActiveTable" class="p-3 mb-3 bg-rose-50 border border-rose-300 rounded-xl text-[11px] text-rose-900 flex items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 mt-0.5"></i>
            <div>
              <span class="font-bold">{{ currentTable }} is currently occupied with active diners.</span>
              <p class="text-[10px] text-rose-800 mt-0.5">Please choose an available (Free) table from the green options above to place your order.</p>
            </div>
          </div>

          <button
            @click="showChangeTableModal = false"
            :disabled="isTableBlockedForOrder || !currentTable"
            class="w-full py-2.5 bg-slate-900 text-white text-xs font-bold rounded-xl hover:bg-slate-800 transition disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ isTableBlockedForOrder ? 'Please Choose an Available Table' : `Confirm ${currentTable}` }}
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

    <!-- Royalty Rewards Club Modal (Visit Stamps, Lucky Spinner & Scratch Card) -->
    <div
      v-if="showLoyaltyModal"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4 overflow-y-auto"
      @click.self="showLoyaltyModal = false"
    >
      <div class="bg-white rounded-3xl max-w-lg w-full shadow-2xl border border-amber-500/30 overflow-hidden flex flex-col max-h-[92vh]">
        <!-- Modal Header -->
        <div class="px-5 py-4 bg-gradient-to-r from-slate-950 via-slate-900 to-amber-950 text-white flex items-center justify-between border-b border-amber-500/20 shrink-0">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 text-slate-950 flex items-center justify-center text-lg font-black shadow">
              <i class="fa-solid fa-crown"></i>
            </div>
            <div>
              <div class="flex items-center gap-2">
                <h3 class="font-extrabold text-sm sm:text-base text-white tracking-wide">Royalty Rewards Club</h3>
                <span class="bg-amber-500/20 text-amber-300 border border-amber-500/40 text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider">
                  {{ restaurant?.name }}
                </span>
              </div>
              <p class="text-[10px] text-amber-200/80">Every visit earns a stamp • Win exclusive discounts!</p>
            </div>
          </div>

          <button
            @click="showLoyaltyModal = false"
            class="w-8 h-8 rounded-full bg-white/10 hover:bg-white/20 text-white/80 hover:text-white flex items-center justify-center transition cursor-pointer"
          >
            <i class="fa-solid fa-xmark text-sm"></i>
          </button>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex border-b border-slate-200 bg-slate-50 px-4 pt-2 gap-2 text-xs font-bold shrink-0">
          <button
            @click="loyaltyModalTab = 'game'"
            :class="[
              'py-2.5 px-4 rounded-t-xl transition border-b-2 cursor-pointer flex items-center gap-2',
              loyaltyModalTab === 'game'
                ? 'bg-white border-amber-500 text-amber-700 shadow-xs'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
          >
            <i class="fa-solid fa-dharmachakra"></i>
            <span>Lucky Game</span>
            <span
              v-if="loyaltyStatus?.customer?.can_play"
              class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"
            ></span>
          </button>

          <button
            @click="loyaltyModalTab = 'stamps'"
            :class="[
              'py-2.5 px-4 rounded-t-xl transition border-b-2 cursor-pointer flex items-center gap-2',
              loyaltyModalTab === 'stamps'
                ? 'bg-white border-amber-500 text-amber-700 shadow-xs'
                : 'border-transparent text-slate-500 hover:text-slate-800'
            ]"
          >
            <i class="fa-solid fa-stamp"></i>
            <span>Visit Stamp Card</span>
            <span
              v-if="loyaltyStatus?.customer"
              class="text-[10px] bg-slate-200 text-slate-700 px-1.5 py-0.2 rounded-full"
            >
              {{ loyaltyStatus.customer.visit_count }}/{{ loyaltyStatus.required_visits }}
            </span>
          </button>
        </div>

        <!-- Body Content -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1">
          <!-- Customer Phone Check if not identified -->
          <div
            v-if="!customerForm.phone && (!loyaltyStatus?.customer || !loyaltyStatus.customer.customer_phone)"
            class="bg-amber-50 border border-amber-300 rounded-2xl p-3.5 text-xs text-amber-950"
          >
            <div class="font-bold flex items-center gap-1.5 mb-1 text-amber-900">
              <i class="fa-solid fa-mobile-screen text-amber-600"></i> Enter your mobile number
            </div>
            <p class="text-[11px] text-amber-800/90 mb-2.5">
              Enter your mobile number to view your earned visit stamps, ticket balance, and unlock rewards!
            </p>
            <div class="flex gap-2">
              <input
                v-model="phoneCheckInput"
                type="tel"
                placeholder="10-digit mobile number"
                class="flex-1 px-3 py-2 bg-white rounded-xl border border-amber-300 text-xs focus:ring-2 focus:ring-amber-500 focus:outline-none"
                @keyup.enter="handlePhoneLookup"
              />
              <button
                @click="handlePhoneLookup"
                :disabled="phoneCheckLoading || !phoneCheckInput"
                class="px-4 py-2 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs rounded-xl shadow transition cursor-pointer disabled:opacity-50"
              >
                <i v-if="phoneCheckLoading" class="fa-solid fa-circle-notch fa-spin"></i>
                <span v-else>Check Stamps</span>
              </button>
            </div>
          </div>

          <!-- TAB 1: Lucky Game (Spinner / Scratch Card) -->
          <div v-if="loyaltyModalTab === 'game'" class="space-y-4">
            <!-- Active Game Mode Switcher if restaurant allows 'both' -->
            <div
              v-if="loyaltyStatus?.loyalty_game_type === 'both' && !activeLoyaltyVoucher"
              class="flex justify-center bg-slate-100 p-1 rounded-2xl max-w-xs mx-auto text-xs font-bold"
            >
              <button
                @click="activeGameType = 'spinner'"
                :class="[
                  'flex-1 py-1.5 px-3 rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer',
                  activeGameType === 'spinner' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'
                ]"
              >
                <i class="fa-solid fa-dharmachakra text-amber-500"></i>
                <span>Lucky Spinner</span>
              </button>
              <button
                @click="switchGameToScratch"
                :class="[
                  'flex-1 py-1.5 px-3 rounded-xl transition flex items-center justify-center gap-1.5 cursor-pointer',
                  activeGameType === 'scratch' ? 'bg-white text-slate-900 shadow-sm' : 'text-slate-500 hover:text-slate-800'
                ]"
              >
                <i class="fa-solid fa-sparkles text-purple-500"></i>
                <span>Scratch Card</span>
              </button>
            </div>

            <!-- Won Voucher Banner if user already holds an unredeemed voucher -->
            <div
              v-if="activeLoyaltyVoucher"
              class="bg-gradient-to-br from-amber-500/20 via-orange-500/20 to-purple-500/20 border-2 border-amber-400 rounded-3xl p-5 text-center shadow-lg relative overflow-hidden"
            >
              <div class="text-4xl mb-2 animate-bounce">🎉</div>
              <div class="inline-block bg-amber-500 text-slate-950 font-black text-[10px] px-3 py-1 rounded-full uppercase tracking-wider mb-2">
                ACTIVE REWARD UNLOCKED
              </div>
              <h4 class="text-lg sm:text-xl font-black text-slate-900">
                {{ activeLoyaltyVoucher.reward_title }}
              </h4>
              <p class="text-xs text-slate-600 mt-1 max-w-sm mx-auto">
                {{ activeLoyaltyVoucher.reward_description }}
              </p>

              <div class="mt-4 p-3 bg-white/90 rounded-2xl border border-amber-300 inline-block font-mono text-xs">
                <span class="text-slate-500 text-[10px] block">Voucher Signature Code:</span>
                <span class="font-extrabold text-amber-700 tracking-wider">
                  {{ activeLoyaltyVoucher.signature ? activeLoyaltyVoucher.signature.substring(0, 16).toUpperCase() : 'REWARD-CLAIMED' }}
                </span>
              </div>

              <div class="mt-4 flex items-center justify-center gap-2">
                <button
                  @click="showLoyaltyModal = false; showCheckoutModal = true;"
                  class="px-5 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-extrabold text-xs rounded-xl shadow-md hover:from-emerald-500 hover:to-teal-500 transition cursor-pointer flex items-center gap-2"
                >
                  <i class="fa-solid fa-cart-shopping"></i>
                  <span>Use at Checkout Now</span>
                </button>
              </div>
            </div>

            <!-- GAME ACTIVE (User has ticket to play!) -->
            <div v-else-if="loyaltyStatus?.customer?.can_play" class="space-y-4 text-center">
              <div class="bg-gradient-to-r from-amber-500/20 to-purple-500/20 border border-amber-400/50 rounded-2xl p-2.5 text-xs text-slate-900 font-extrabold flex items-center justify-center gap-2">
                <span class="text-base">🎟️</span>
                <span>You have 1 Lucky Reward Ticket! {{ activeGameType === 'spinner' ? 'Spin the wheel below to win:' : 'Scratch the card below to reveal your prize:' }}</span>
              </div>

              <!-- SPINNER WHEEL VIEW -->
              <div v-if="activeGameType === 'spinner'" class="flex flex-col items-center justify-center py-2">
                <div class="relative w-[300px] h-[300px] sm:w-[320px] sm:h-[320px]">
                  <!-- Needle pointer at 12 o'clock -->
                  <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-20 w-0 h-0 border-l-[14px] border-l-transparent border-r-[14px] border-r-transparent border-t-[28px] border-t-amber-500 drop-shadow-md"></div>

                  <!-- Canvas wheel with CSS rotation -->
                  <canvas
                    ref="spinnerCanvasRef"
                    width="320"
                    height="320"
                    class="rounded-full shadow-2xl border-4 border-amber-400 transition-transform"
                    :style="{
                      transform: `rotate(${spinnerRotation}deg)`,
                      transitionDuration: isSpinning ? '4.5s' : '0s',
                      transitionTimingFunction: 'cubic-bezier(0.15, 0.9, 0.25, 1)'
                    }"
                  ></canvas>

                  <!-- Center Spin button -->
                  <button
                    @click="spinWheel"
                    :disabled="isSpinning"
                    class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-10 w-16 h-16 rounded-full bg-gradient-to-tr from-amber-400 via-amber-500 to-orange-500 text-slate-950 font-black text-xs uppercase tracking-wider shadow-xl border-4 border-white flex flex-col items-center justify-center cursor-pointer hover:scale-105 active:scale-95 transition disabled:opacity-80"
                  >
                    <i class="fa-solid fa-play text-sm"></i>
                    <span class="text-[9px] font-black mt-0.5">{{ isSpinning ? '...' : 'SPIN' }}</span>
                  </button>
                </div>

                <p class="text-[11px] text-slate-500 mt-4">
                  Tap <strong>SPIN</strong> to spin the lucky wheel and claim your discount!
                </p>
              </div>

              <!-- SCRATCH CARD VIEW -->
              <div v-else class="flex flex-col items-center justify-center py-2">
                <div class="relative w-[300px] h-[180px] rounded-3xl overflow-hidden shadow-xl border-2 border-purple-400">
                  <!-- Hidden Prize Underneath -->
                  <div class="absolute inset-0 bg-gradient-to-br from-amber-400/20 via-pink-400/20 to-purple-400/30 flex flex-col items-center justify-center p-4 text-center">
                    <div class="text-3xl mb-1">🎁</div>
                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-700">LUCKY PRIZE</span>
                    <div class="text-base font-black text-slate-900 leading-tight">
                      {{ mysteryScratchReward?.title || 'Mega Discount Offer' }}
                    </div>
                    <div class="text-[11px] text-slate-600 mt-0.5">
                      {{ mysteryScratchReward?.description || 'Exclusive Loyalty Reward' }}
                    </div>
                  </div>

                  <!-- Scratch Canvas on Top -->
                  <canvas
                    ref="scratchCanvasRef"
                    width="300"
                    height="180"
                    class="absolute inset-0 cursor-crosshair touch-none select-none z-10 transition-opacity duration-500"
                    :class="{ 'opacity-0 pointer-events-none': scratchRevealed }"
                    @mousedown="startScratch"
                    @mousemove="doScratch"
                    @mouseup="endScratch"
                    @mouseleave="endScratch"
                    @touchstart="startScratchTouch"
                    @touchmove="doScratchTouch"
                    @touchend="endScratch"
                  ></canvas>
                </div>

                <div class="flex items-center gap-2 mt-3 text-xs text-slate-600">
                  <i class="fa-solid fa-hand-pointer text-purple-500 animate-bounce"></i>
                  <span>Scratch with finger or mouse to reveal your reward!</span>
                  <span v-if="scratchPercent > 0" class="font-bold text-purple-600">({{ scratchPercent }}%)</span>
                </div>
              </div>
            </div>

            <!-- LOCKED STATE (Customer doesn't have ticket yet) -->
            <div v-else class="space-y-4">
              <div class="bg-slate-50 border border-slate-200 rounded-3xl p-5 text-center relative overflow-hidden">
                <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-600 flex items-center justify-center text-2xl mx-auto mb-3 shadow-inner">
                  <i class="fa-solid fa-lock"></i>
                </div>
                <h4 class="font-extrabold text-sm sm:text-base text-slate-900">
                  Lucky Reward Wheel Locked
                </h4>
                <p class="text-xs text-slate-600 mt-1 max-w-sm mx-auto">
                  <template v-if="loyaltyStatus?.customer && loyaltyStatus.customer.visit_count > 0">
                    Aapne <strong>{{ loyaltyStatus.customer.visit_count }}</strong> visit(s) kiye hain. <strong>{{ loyaltyStatus.customer.visits_remaining }}</strong> aur visit karne par Lucky Ticket unlock hoga!
                  </template>
                  <template v-else>
                    Har distinct order date par 1 stamp milta hai. <strong>{{ loyaltyStatus?.required_visits || 5 }}</strong> visits poori karne par Lucky Spinner / Scratch card unlock hoga!
                  </template>
                </p>

                <!-- Mini Progress Bar -->
                <div class="mt-4 max-w-xs mx-auto">
                  <div class="flex justify-between text-[11px] font-bold text-slate-600 mb-1">
                    <span>Progress</span>
                    <span>{{ loyaltyStatus?.customer?.visit_count || 0 }} / {{ loyaltyStatus?.required_visits || 5 }} Visits</span>
                  </div>
                  <div class="w-full h-3 bg-slate-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-gradient-to-r from-amber-500 to-orange-500 transition-all duration-500"
                      :style="{ width: `${Math.min(100, (((loyaltyStatus?.customer?.visit_count || 0) % (loyaltyStatus?.required_visits || 5)) / (loyaltyStatus?.required_visits || 5)) * 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>

              <!-- Available Prizes Showcase -->
              <div>
                <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                  <i class="fa-solid fa-gift text-amber-500"></i> Exciting Prizes Waiting to be Won:
                </h5>
                <div class="grid grid-cols-2 gap-2 text-xs">
                  <div
                    v-for="rew in (loyaltyStatus?.rewards || [])"
                    :key="rew.id"
                    class="p-2.5 rounded-2xl border border-slate-200 bg-white flex items-center gap-2.5 shadow-xs"
                  >
                    <div
                      class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-black shrink-0 text-white"
                      :style="{ backgroundColor: rew.color || '#f59e0b' }"
                    >
                      <i :class="rew.icon || 'fa-solid fa-gift'"></i>
                    </div>
                    <div class="truncate">
                      <div class="font-extrabold text-slate-900 truncate">{{ rew.title }}</div>
                      <div class="text-[10px] text-slate-500 truncate">{{ rew.description }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- TAB 2: Visit Stamp Card -->
          <div v-else-if="loyaltyModalTab === 'stamps'" class="space-y-4">
            <div class="bg-gradient-to-br from-amber-500/10 via-orange-500/10 to-yellow-500/10 border-2 border-amber-300 rounded-3xl p-5 shadow-sm">
              <div class="flex items-center justify-between mb-4">
                <div>
                  <h4 class="font-black text-sm text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-passport text-amber-600"></i> Royal Stamp Passport
                  </h4>
                  <p class="text-[10px] text-slate-600">1 visit stamp per distinct order date</p>
                </div>
                <span class="text-xs font-black text-amber-900 bg-amber-100 border border-amber-300 px-2.5 py-1 rounded-full">
                  {{ loyaltyStatus?.customer?.visit_count || 0 }} Total Visits
                </span>
              </div>

              <!-- Stamp Slots Grid -->
              <div class="grid grid-cols-5 gap-2.5 my-4">
                <div
                  v-for="index in (loyaltyStatus?.required_visits || 5)"
                  :key="index"
                  :class="[
                    'aspect-square rounded-2xl flex flex-col items-center justify-center p-1 text-center border-2 transition',
                    index <= ((loyaltyStatus?.customer?.visit_count || 0) % (loyaltyStatus?.required_visits || 5) === 0 && (loyaltyStatus?.customer?.visit_count || 0) > 0 ? (loyaltyStatus?.required_visits || 5) : ((loyaltyStatus?.customer?.visit_count || 0) % (loyaltyStatus?.required_visits || 5)))
                      ? 'bg-gradient-to-tr from-amber-400 to-yellow-300 border-amber-500 text-slate-950 font-black shadow-md scale-102'
                      : 'bg-white/80 border-dashed border-slate-300 text-slate-400'
                  ]"
                >
                  <template v-if="index <= ((loyaltyStatus?.customer?.visit_count || 0) % (loyaltyStatus?.required_visits || 5) === 0 && (loyaltyStatus?.customer?.visit_count || 0) > 0 ? (loyaltyStatus?.required_visits || 5) : ((loyaltyStatus?.customer?.visit_count || 0) % (loyaltyStatus?.required_visits || 5)))">
                    <i class="fa-solid fa-crown text-base text-amber-950"></i>
                    <span class="text-[9px] font-black uppercase mt-0.5">Stamp #{{ index }}</span>
                  </template>
                  <template v-else>
                    <i class="fa-solid fa-lock text-xs text-slate-300"></i>
                    <span class="text-[9px] font-semibold mt-0.5">#{{ index }}</span>
                  </template>
                </div>
              </div>

              <div class="text-[11px] text-slate-600 bg-white/70 p-3 rounded-2xl border border-amber-200/60 leading-relaxed">
                <i class="fa-solid fa-circle-info text-amber-600 mr-1"></i>
                <template v-if="loyaltyStatus?.customer && loyaltyStatus.customer.can_play">
                  Milestone reached! Aapke paas <strong>{{ loyaltyStatus.customer.available_tickets }} Lucky Ticket</strong> ready hai! Lucky Game tab par jaakar spin karein!
                </template>
                <template v-else-if="loyaltyStatus?.customer && loyaltyStatus.customer.visit_count > 0">
                  Aapko Lucky Spinner ticket paane ke liye <strong>{{ loyaltyStatus.customer.visits_remaining }}</strong> aur distinct date visit(s) ki zaroorat hai.
                </template>
                <template v-else>
                  Is restaurant se order karne par har din ek stamp milega. <strong>{{ loyaltyStatus?.required_visits || 5 }}</strong> stamps poore hote hi Lucky Wheel spin karne ka mauka milega!
                </template>
              </div>
            </div>

            <!-- Recent Visits History if available -->
            <div v-if="loyaltyStatus?.customer?.recent_visits && loyaltyStatus.customer.recent_visits.length > 0">
              <h5 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Recent Visit Dates:</h5>
              <div class="space-y-1.5 max-h-40 overflow-y-auto">
                <div
                  v-for="(vDate, idx) in loyaltyStatus.customer.recent_visits"
                  :key="idx"
                  class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs"
                >
                  <span class="flex items-center gap-2 font-medium text-slate-800">
                    <i class="fa-solid fa-calendar-check text-emerald-600"></i>
                    {{ vDate }}
                  </span>
                  <span class="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
                    ✓ Verified Visit Stamp
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
          <button
            @click="showLoyaltyModal = false"
            class="px-4 py-2 bg-white hover:bg-slate-100 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 transition cursor-pointer"
          >
            Close
          </button>

          <button
            v-if="activeLoyaltyVoucher"
            @click="showLoyaltyModal = false; showCheckoutModal = true;"
            class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-xs rounded-xl shadow transition cursor-pointer flex items-center gap-1.5"
          >
            <span>Proceed to Checkout</span>
            <i class="fa-solid fa-arrow-right"></i>
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

    <!-- Floating Table Service Bell Button (v-if="restaurant?.enable_waiter_call") -->
    <div
      v-if="restaurant?.enable_waiter_call && orderType === 'dine_in'"
      class="fixed bottom-24 left-4 z-40"
    >
      <button
        @click="showServiceBellModal = true"
        class="flex items-center gap-2 px-3.5 py-2.5 bg-slate-900 hover:bg-slate-800 text-amber-400 rounded-full shadow-2xl border-2 border-amber-400/80 font-black text-xs transition transform hover:scale-105 active:scale-95 cursor-pointer group"
      >
        <span class="w-6 h-6 rounded-full bg-amber-400 text-slate-950 flex items-center justify-center text-xs shadow group-hover:animate-bounce">
          <i class="fa-solid fa-bell"></i>
        </span>
        <span class="text-white font-bold">{{ currentLang === 'hi' ? 'वेटर बुलाएं' : 'Call Waiter' }}</span>
      </button>
    </div>

    <!-- Table Service Bell Modal -->
    <div
      v-if="showServiceBellModal"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4"
      @click.self="showServiceBellModal = false"
    >
      <div class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl text-slate-900 border border-slate-200">
        <!-- Header -->
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-2xl bg-amber-500/20 text-amber-600 flex items-center justify-center text-base border border-amber-500/30">
              <i class="fa-solid fa-bell"></i>
            </div>
            <div>
              <h3 class="font-extrabold text-sm text-slate-900">
                {{ currentLang === 'hi' ? 'टेबल सर्विस बेल' : 'Table Service Bell' }}
              </h3>
              <p class="text-[11px] text-slate-500">
                {{ currentLang === 'hi' ? 'सीधे स्टाफ को रिक्वेस्ट भेजें' : 'Instant 1-tap staff alert for' }} <strong>{{ currentTable }}</strong>
              </p>
            </div>
          </div>

          <button
            @click="showServiceBellModal = false"
            class="w-7 h-7 rounded-full bg-slate-100 hover:bg-slate-200 text-slate-500 flex items-center justify-center cursor-pointer"
          >
            <i class="fa-solid fa-xmark text-xs"></i>
          </button>
        </div>

        <!-- Success Message -->
        <div v-if="serviceBellSuccess" class="p-4 bg-emerald-50 border border-emerald-300 rounded-2xl text-emerald-800 text-center space-y-1 mb-4">
          <div class="w-10 h-10 rounded-full bg-emerald-500 text-white flex items-center justify-center text-lg mx-auto shadow">
            <i class="fa-solid fa-check"></i>
          </div>
          <div class="font-black text-xs uppercase tracking-wide">
            {{ currentLang === 'hi' ? 'स्टाफ को सूचित कर दिया गया है!' : 'Staff Alerted Successfully!' }}
          </div>
          <p class="text-[11px] text-emerald-700">
            {{ currentLang === 'hi' ? 'वेटर जल्द ही आपकी टेबल पर पहुंच रहे हैं।' : 'A waiter will attend your table shortly.' }}
          </p>
        </div>

        <!-- Request Options Grid -->
        <div v-else class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 mb-2">
              {{ currentLang === 'hi' ? 'आपको क्या चाहिए?' : 'What do you need?' }}
            </label>
            <div class="grid grid-cols-2 gap-2">
              <button
                v-for="opt in [
                  { id: 'water', label: 'Drinking Water', hi: 'पीने का पानी', icon: 'fa-solid fa-glass-water', color: 'text-cyan-500' },
                  { id: 'waiter', label: 'Call Waiter', hi: 'वेटर बुलाएं', icon: 'fa-solid fa-bell', color: 'text-amber-500' },
                  { id: 'cutlery', label: 'Extra Cutlery', hi: 'चम्मच / प्लेट्स', icon: 'fa-solid fa-utensils', color: 'text-purple-500' },
                  { id: 'bill', label: 'Get Bill', hi: 'बिल चाहिए', icon: 'fa-solid fa-file-invoice', color: 'text-emerald-500' },
                  { id: 'cleaning', label: 'Clean Table', hi: 'टेबल साफ करें', icon: 'fa-solid fa-broom', color: 'text-blue-500' },
                  { id: 'other', label: 'Other Help', hi: 'अन्य सहायता', icon: 'fa-solid fa-hand', color: 'text-rose-500' }
                ]"
                :key="opt.id"
                type="button"
                @click="selectedServiceType = opt.id"
                :class="[
                  'p-3 rounded-2xl border-2 flex items-center gap-2.5 transition text-left cursor-pointer',
                  selectedServiceType === opt.id ? 'border-amber-500 bg-amber-50/70 shadow-sm' : 'border-slate-200 hover:border-slate-300 bg-slate-50/50'
                ]"
              >
                <div :class="['text-base', opt.color]">
                  <i :class="opt.icon"></i>
                </div>
                <div>
                  <div class="font-black text-xs text-slate-900 leading-tight">
                    {{ currentLang === 'hi' ? opt.hi : opt.label }}
                  </div>
                  <div class="text-[9px] text-slate-500">
                    {{ currentLang === 'hi' ? opt.label : opt.hi }}
                  </div>
                </div>
              </button>
            </div>
          </div>

          <!-- Optional Notes -->
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              {{ currentLang === 'hi' ? 'कोई विशेष नोट? (वैकल्पिक)' : 'Special Request Note (Optional)' }}
            </label>
            <input
              v-model="serviceBellNotes"
              type="text"
              :placeholder="currentLang === 'hi' ? 'उदा. ठंडा पानी, एक्स्ट्रा नैपकिन...' : 'e.g. Warm water, extra napkins, spicy chutney...'"
              class="w-full px-3 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs text-slate-900 focus:outline-none focus:ring-2 focus:ring-amber-500/30"
            />
          </div>

          <!-- Submit Button -->
          <button
            @click="submitServiceCall"
            :disabled="submittingServiceCall"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black rounded-xl text-xs shadow-lg transition flex items-center justify-center gap-2 cursor-pointer disabled:opacity-50"
          >
            <i v-if="submittingServiceCall" class="fa-solid fa-circle-notch fa-spin"></i>
            <i v-else class="fa-solid fa-paper-plane"></i>
            <span>{{ currentLang === 'hi' ? 'रिक्वेस्ट भेजें' : 'Send Request to Staff' }}</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import QRCode from 'qrcode';
import html2pdf from 'html2pdf.js';
import { playSound, launchConfetti, sendSystemNotification, requestNotificationPermission } from '../utils/notification.js';

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

// Language & Voice Search State
const currentLang = ref(localStorage.getItem('qrmenu_lang') || 'en');
const isListening = ref(false);
let speechRecognitionInstance = null;

const toggleLanguage = () => {
  currentLang.value = currentLang.value === 'en' ? 'hi' : 'en';
  localStorage.setItem('qrmenu_lang', currentLang.value);
};

const toggleVoiceSearch = () => {
  const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
  if (!SpeechRecognition) {
    alert('Voice search is not supported by your browser. Please type in the search bar.');
    return;
  }

  if (isListening.value) {
    speechRecognitionInstance?.stop();
    isListening.value = false;
    return;
  }

  try {
    speechRecognitionInstance = new SpeechRecognition();
    speechRecognitionInstance.lang = currentLang.value === 'hi' ? 'hi-IN' : 'en-IN';
    speechRecognitionInstance.continuous = false;
    speechRecognitionInstance.interimResults = false;

    speechRecognitionInstance.onstart = () => {
      isListening.value = true;
    };

    speechRecognitionInstance.onresult = (event) => {
      const transcript = event.results[0][0].transcript;
      searchQuery.value = transcript;
      isListening.value = false;
    };

    speechRecognitionInstance.onerror = () => {
      isListening.value = false;
    };

    speechRecognitionInstance.onend = () => {
      isListening.value = false;
    };

    speechRecognitionInstance.start();
  } catch (err) {
    console.error('Speech recognition error', err);
    isListening.value = false;
  }
};

// Table Service Bell State & Actions
const showServiceBellModal = ref(false);
const selectedServiceType = ref('water');
const serviceBellNotes = ref('');
const submittingServiceCall = ref(false);
const serviceBellSuccess = ref(false);

const submitServiceCall = async () => {
  if (!restaurant.value) return;
  submittingServiceCall.value = true;
  try {
    const res = await axios.post(`/r/${restaurant.value.slug}/service-call`, {
      table_number: currentTable.value || 'Table 1',
      service_type: selectedServiceType.value,
      notes: serviceBellNotes.value,
    });
    if (res.data.success) {
      playSound('status_update');
      serviceBellSuccess.value = true;
      setTimeout(() => {
        serviceBellSuccess.value = false;
        showServiceBellModal.value = false;
        serviceBellNotes.value = '';
      }, 3000);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit service call');
  } finally {
    submittingServiceCall.value = false;
  }
};

// Cart: [{ id, name, price, quantity }]
const cart = ref([]);

const customerForm = ref({
  name: localStorage.getItem('qrmenu_customer_name') || '',
  phone: localStorage.getItem('qrmenu_customer_phone') || '',
  address: localStorage.getItem('qrmenu_customer_address') || '',
  notes: '',
});
const paymentMethod = ref('upi');
const submittingOrder = ref(false);
const orderError = ref('');

// Royalty Rewards State
const loyaltyStatus = ref(null);
const showLoyaltyModal = ref(false);
const activeLoyaltyVoucher = ref(null);
const loyaltyModalTab = ref('game'); // 'game' or 'stamps'
const activeGameType = ref('spinner'); // 'spinner' or 'scratch'
const isSpinning = ref(false);
const spinnerRotation = ref(0);
const spinnerCanvasRef = ref(null);
const scratchCanvasRef = ref(null);
const scratchPercent = ref(0);
const scratchRevealed = ref(false);
const isScratching = ref(false);
const phoneCheckInput = ref(localStorage.getItem('qrmenu_customer_phone') || '');
const phoneCheckLoading = ref(false);
const claimingReward = ref(false);
const mysteryScratchReward = ref(null);

// 5 KM Geofence & Location Notification State
const userLocation = ref(null);
const userDistanceKm = ref(null);
const isWithinGeofence = ref(false);
const showNearbyAlert = ref(false);
const locationChecked = ref(false);
let geofenceWatchId = null;

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

      // Default order type fallback if disabled
      if (restaurant.value.enable_home_delivery === false && orderType.value === 'delivery') {
        orderType.value = 'dine_in';
      }
      if (restaurant.value.enable_takeaway === false && orderType.value === 'takeaway') {
        orderType.value = 'dine_in';
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

      // Load Royalty Rewards status & saved voucher
      if (restaurant.value.loyalty_enabled !== false) {
        if (restaurant.value.loyalty_game_type === 'scratch_card') {
          activeGameType.value = 'scratch';
        }
        fetchLoyaltyStatus(customerForm.value.phone);
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

const isMyActiveTable = computed(() => {
  if (!currentTable.value || !restaurant.value) return false;
  // If user came via "Order More Items" with continue_order in URL
  if (route.query.continue_order) return true;

  // If table matches stored active table for this restaurant
  const savedTable = localStorage.getItem(`qrmenu_active_table_${restaurant.value.id}`);
  const savedPhone = localStorage.getItem('qrmenu_customer_phone');
  if (savedTable && savedTable === currentTable.value) {
    if (!savedPhone || !customerForm.value.phone || customerForm.value.phone === savedPhone) {
      return true;
    }
  }
  return false;
});

const isTableBlockedForOrder = computed(() => {
  return orderType.value === 'dine_in' && selectedTableIsOccupied.value && !isMyActiveTable.value;
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
  if (tableObj.status === 'occupied' && !(tableObj.name === currentTable.value && isMyActiveTable.value)) {
    return;
  }
  currentTable.value = tableObj.name;
  showChangeTableModal.value = false;
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

const loyaltyDiscountAmount = computed(() => {
  if (!activeLoyaltyVoucher.value) return 0;
  const v = activeLoyaltyVoucher.value;
  const sub = cartSubtotal.value;
  const val = Number(v.discount_value ?? v.value) || 0;
  const type = v.reward_type || v.type;

  if (type === 'percent' || type === 'percentage') {
    return Math.round(((sub * val) / 100) * 100) / 100;
  }
  if (type === 'flat') {
    return Math.min(sub, val);
  }
  if (type === 'bogo' || type === 'free_item') {
    if (val > 0) return Math.min(sub, val);
    return cart.value.length > 0 ? Math.min(sub, cart.value[0].price) : 0;
  }
  return Math.min(sub, val);
});

const discountedSubtotal = computed(() => {
  return Math.max(0, cartSubtotal.value - loyaltyDiscountAmount.value);
});

const taxAmount = computed(() => {
  const taxPercent = restaurant.value?.tax_percentage || 0;
  return (discountedSubtotal.value * taxPercent) / 100;
});

const grandTotal = computed(() => {
  return discountedSubtotal.value + taxAmount.value;
});

// Royalty Rewards Helper Functions
const fetchLoyaltyStatus = async (phone) => {
  if (!restaurant.value) return;
  try {
    const p = phone !== undefined ? phone : customerForm.value.phone;
    const res = await axios.get(`/r/${restaurant.value.slug}/loyalty-status?phone=${encodeURIComponent(p || '')}`);
    if (res.data.success) {
      loyaltyStatus.value = res.data;
      if (res.data.loyalty_game_type === 'scratch_card') {
        activeGameType.value = 'scratch';
      }
      // Restore active voucher from local storage if available
      const savedVoucher = localStorage.getItem(`qrmenu_loyalty_voucher_${restaurant.value.id}`);
      if (savedVoucher && !activeLoyaltyVoucher.value) {
        try {
          activeLoyaltyVoucher.value = JSON.parse(savedVoucher);
        } catch (e) {}
      }

      if (userLocation.value) {
        checkGeofenceNotification(userLocation.value.lat, userLocation.value.lng);
      }
    }
  } catch (err) {
    console.error('Failed to load loyalty status', err);
  }
};

const drawSpinnerWheel = () => {
  const canvas = spinnerCanvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  const rewards = loyaltyStatus.value?.rewards?.length
    ? loyaltyStatus.value.rewards
    : [
        { title: '₹100 OFF', color: '#f59e0b' },
        { title: '40% OFF', color: '#8b5cf6' },
        { title: 'BOGO FREE', color: '#10b981' },
        { title: '₹50 OFF', color: '#3b82f6' },
        { title: 'FREE DESSERT', color: '#ec4899' },
        { title: '25% SPECIAL', color: '#f97316' },
      ];

  const numSlices = rewards.length;
  const arc = (2 * Math.PI) / numSlices;
  const width = canvas.width;
  const height = canvas.height;
  const centerX = width / 2;
  const centerY = height / 2;
  const radius = centerX - 12;

  ctx.clearRect(0, 0, width, height);

  // Draw slices
  rewards.forEach((reward, i) => {
    const angle = i * arc;
    ctx.beginPath();
    ctx.fillStyle = reward.color || '#f59e0b';
    ctx.moveTo(centerX, centerY);
    ctx.arc(centerX, centerY, radius, angle, angle + arc);
    ctx.lineTo(centerX, centerY);
    ctx.fill();

    ctx.strokeStyle = '#ffffff';
    ctx.lineWidth = 2.5;
    ctx.stroke();

    // Draw slice text
    ctx.save();
    ctx.translate(centerX, centerY);
    ctx.rotate(angle + arc / 2);
    ctx.textAlign = 'right';
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 12px Inter, sans-serif';
    ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
    ctx.shadowBlur = 4;
    ctx.fillText(reward.title || 'Offer', radius - 18, 5);
    ctx.restore();
  });

  // Outer rim
  ctx.beginPath();
  ctx.arc(centerX, centerY, radius, 0, 2 * Math.PI);
  ctx.strokeStyle = '#d97706';
  ctx.lineWidth = 4;
  ctx.stroke();

  // Center hub
  ctx.beginPath();
  ctx.arc(centerX, centerY, 32, 0, 2 * Math.PI);
  ctx.fillStyle = '#ffffff';
  ctx.shadowColor = 'rgba(0, 0, 0, 0.2)';
  ctx.shadowBlur = 8;
  ctx.fill();
  ctx.lineWidth = 3;
  ctx.strokeStyle = '#f59e0b';
  ctx.stroke();
};

const spinWheel = async () => {
  if (isSpinning.value || !loyaltyStatus.value?.customer?.can_play) return;

  const rewards = loyaltyStatus.value?.rewards?.length
    ? loyaltyStatus.value.rewards
    : [
        { id: 1, title: '₹100 OFF', reward_type: 'flat', discount_value: 100 },
        { id: 2, title: '40% OFF', reward_type: 'percentage', discount_value: 40 },
        { id: 3, title: 'BOGO FREE', reward_type: 'bogo', discount_value: 0 },
        { id: 4, title: '₹50 OFF', reward_type: 'flat', discount_value: 50 },
        { id: 5, title: 'FREE DESSERT', reward_type: 'free_item', discount_value: 0 },
        { id: 6, title: '25% SPECIAL', reward_type: 'percentage', discount_value: 25 },
      ];

  isSpinning.value = true;
  const numSlices = rewards.length;
  const winningIndex = Math.floor(Math.random() * numSlices);
  const winningReward = rewards[winningIndex];

  // Canvas arc is oriented with 0 at 3 o'clock. Needle is at 12 o'clock (270 deg).
  const sliceDeg = 360 / numSlices;
  const sliceCenterDeg = winningIndex * sliceDeg + sliceDeg / 2;
  const targetDeg = 270 - sliceCenterDeg;
  const normDeg = (deg) => ((deg % 360) + 360) % 360;
  const currentNorm = normDeg(spinnerRotation.value);
  const delta = normDeg(targetDeg - currentNorm);
  const finalRotation = spinnerRotation.value + 360 * 5 + delta;

  spinnerRotation.value = finalRotation;
  playSound('status_update');

  setTimeout(async () => {
    isSpinning.value = false;
    await claimLoyaltyReward(winningReward);
  }, 4600);
};

const initScratchCanvas = () => {
  const canvas = scratchCanvasRef.value;
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  scratchRevealed.value = false;
  scratchPercent.value = 0;

  const rewards = loyaltyStatus.value?.rewards || [];
  if (rewards.length > 0) {
    mysteryScratchReward.value = rewards[Math.floor(Math.random() * rewards.length)];
  }

  ctx.globalCompositeOperation = 'source-over';
  const grad = ctx.createLinearGradient(0, 0, canvas.width, canvas.height);
  grad.addColorStop(0, '#94a3b8');
  grad.addColorStop(0.3, '#f1f5f9');
  grad.addColorStop(0.6, '#cbd5e1');
  grad.addColorStop(1, '#64748b');

  ctx.fillStyle = grad;
  ctx.fillRect(0, 0, canvas.width, canvas.height);

  ctx.fillStyle = '#f59e0b';
  for (let i = 0; i < 20; i++) {
    const x = Math.random() * canvas.width;
    const y = Math.random() * canvas.height;
    ctx.beginPath();
    ctx.arc(x, y, Math.random() * 2 + 1, 0, 2 * Math.PI);
    ctx.fill();
  }

  ctx.fillStyle = '#1e293b';
  ctx.textAlign = 'center';
  ctx.font = 'bold 15px Inter, sans-serif';
  ctx.fillText('✨ SCRATCH TO REVEAL ✨', canvas.width / 2, canvas.height / 2 - 6);

  ctx.fillStyle = '#475569';
  ctx.font = '11px Inter, sans-serif';
  ctx.fillText('Rub with finger or mouse', canvas.width / 2, canvas.height / 2 + 16);
};

const checkScratchPercentage = () => {
  const canvas = scratchCanvasRef.value;
  if (!canvas || scratchRevealed.value) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  try {
    const imgData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const data = imgData.data;
    let transparentPixels = 0;
    const totalPixels = data.length / 4;
    for (let i = 3; i < data.length; i += 16) {
      if (data[i] === 0) transparentPixels += 4;
    }
    const percent = Math.min(100, Math.round((transparentPixels / totalPixels) * 100));
    scratchPercent.value = percent;

    if (percent >= 38 && !scratchRevealed.value) {
      scratchRevealed.value = true;
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      claimLoyaltyReward(mysteryScratchReward.value);
    }
  } catch (e) {}
};

const scratchAt = (x, y) => {
  const canvas = scratchCanvasRef.value;
  if (!canvas || scratchRevealed.value) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  ctx.globalCompositeOperation = 'destination-out';
  ctx.beginPath();
  ctx.arc(x, y, 22, 0, 2 * Math.PI);
  ctx.fill();
  checkScratchPercentage();
};

const startScratch = (e) => {
  isScratching.value = true;
  const rect = scratchCanvasRef.value.getBoundingClientRect();
  scratchAt(e.clientX - rect.left, e.clientY - rect.top);
};

const doScratch = (e) => {
  if (!isScratching.value) return;
  const rect = scratchCanvasRef.value.getBoundingClientRect();
  scratchAt(e.clientX - rect.left, e.clientY - rect.top);
};

const endScratch = () => {
  isScratching.value = false;
};

const startScratchTouch = (e) => {
  isScratching.value = true;
  if (e.touches && e.touches[0]) {
    const rect = scratchCanvasRef.value.getBoundingClientRect();
    scratchAt(e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top);
  }
};

const doScratchTouch = (e) => {
  if (!isScratching.value) return;
  if (e.touches && e.touches[0]) {
    e.preventDefault();
    const rect = scratchCanvasRef.value.getBoundingClientRect();
    scratchAt(e.touches[0].clientX - rect.left, e.touches[0].clientY - rect.top);
  }
};

const claimLoyaltyReward = async (reward) => {
  if (!restaurant.value || claimingReward.value) return;
  claimingReward.value = true;
  try {
    const phone = customerForm.value.phone || phoneCheckInput.value;
    const res = await axios.post(`/r/${restaurant.value.slug}/claim-loyalty-reward`, {
      phone: phone,
      customer_phone: phone,
      reward_id: reward?.id || null,
    });
    if (res.data.success) {
      activeLoyaltyVoucher.value = res.data.voucher;
      localStorage.setItem(`qrmenu_loyalty_voucher_${restaurant.value.id}`, JSON.stringify(res.data.voucher));
      launchConfetti();
      playSound('payment_success');
      await fetchLoyaltyStatus(phone);
    }
  } catch (err) {
    alert(err.response?.data?.message || 'Could not claim reward.');
  } finally {
    claimingReward.value = false;
  }
};

const removeVoucher = () => {
  activeLoyaltyVoucher.value = null;
  if (restaurant.value) {
    localStorage.removeItem(`qrmenu_loyalty_voucher_${restaurant.value.id}`);
  }
};

const openLoyaltyModal = async () => {
  showLoyaltyModal.value = true;
  const phone = customerForm.value.phone || phoneCheckInput.value;
  await fetchLoyaltyStatus(phone);
  await nextTick();
  if (activeGameType.value === 'spinner') {
    drawSpinnerWheel();
  } else if (activeGameType.value === 'scratch') {
    initScratchCanvas();
  }
};

const switchGameToScratch = async () => {
  activeGameType.value = 'scratch';
  await nextTick();
  initScratchCanvas();
};

const handlePhoneLookup = async () => {
  if (!phoneCheckInput.value) return;
  phoneCheckLoading.value = true;
  try {
    customerForm.value.phone = phoneCheckInput.value;
    localStorage.setItem('qrmenu_customer_phone', phoneCheckInput.value);
    await fetchLoyaltyStatus(phoneCheckInput.value);
    await nextTick();
    if (activeGameType.value === 'spinner') {
      drawSpinnerWheel();
    } else {
      initScratchCanvas();
    }
  } finally {
    phoneCheckLoading.value = false;
  }
};

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
  if (orderType.value === 'dine_in' && (!currentTable.value || (selectedTableIsOccupied.value && !isMyActiveTable.value))) {
    orderError.value = `${currentTable.value || 'Selected table'} is currently occupied. Please choose an available free table.`;
    return;
  }

  orderError.value = '';
  submittingOrder.value = true;

  try {
    const isSalon = restaurant.value.business_type === 'salon';
    const payload = {
      restaurant_id: restaurant.value.id,
      continue_order: route.query.continue_order || null,
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
      loyalty_voucher: activeLoyaltyVoucher.value,
    };

    const res = await axios.post('/orders', payload);

    if (res.data.success) {
      cart.value = [];
      showCheckoutModal.value = false;
      if (activeLoyaltyVoucher.value) {
        localStorage.removeItem(`qrmenu_loyalty_voucher_${restaurant.value.id}`);
        activeLoyaltyVoucher.value = null;
      }
      // Persist customer details and active table
      localStorage.setItem('qrmenu_customer_name', customerForm.value.name);
      localStorage.setItem('qrmenu_customer_phone', customerForm.value.phone);
      if (orderType.value === 'delivery') {
        localStorage.setItem('qrmenu_customer_address', customerForm.value.address || '');
      }
      if (orderType.value === 'dine_in' && currentTable.value) {
        localStorage.setItem(`qrmenu_active_table_${restaurant.value.id}`, currentTable.value);
      }
      // Redirect to Order tracking / receipt page
      router.push(`/order-track/${res.data.order.order_code}`);
    }
  } catch (err) {
    orderError.value = err.response?.data?.message || 'Failed to place order. Please try again.';
  } finally {
    submittingOrder.value = false;
  }
};

// 5 KM Geofence & Location Notification Methods
const calculateDistanceKm = (lat1, lon1, lat2, lon2) => {
  const R = 6371; // Earth's radius in KM
  const dLat = ((lat2 - lat1) * Math.PI) / 180;
  const dLon = ((lon2 - lon1) * Math.PI) / 180;
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos((lat1 * Math.PI) / 180) *
      Math.cos((lat2 * Math.PI) / 180) *
      Math.sin(dLon / 2) *
      Math.sin(dLon / 2);
  const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
  return R * c;
};

const checkGeofenceNotification = (lat, lng) => {
  if (!restaurant.value) return;

  const restLat = restaurant.value.latitude || loyaltyStatus.value?.latitude;
  const restLng = restaurant.value.longitude || loyaltyStatus.value?.longitude;
  const radius = restaurant.value.geofence_radius_km || loyaltyStatus.value?.geofence_radius_km || 5.0;

  if (!restLat || !restLng) {
    return;
  }

  const dist = calculateDistanceKm(lat, lng, restLat, restLng);
  userDistanceKm.value = dist;
  isWithinGeofence.value = dist <= radius;

  if (isWithinGeofence.value) {
    const cacheKey = `qrmenu_nearby_notif_${restaurant.value.id}`;
    const lastNotified = localStorage.getItem(cacheKey);
    const now = Date.now();

    // Trigger notification if not notified in last 2 hours
    if (!lastNotified || now - Number(lastNotified) > 2 * 60 * 60 * 1000) {
      showNearbyAlert.value = true;
      localStorage.setItem(cacheKey, String(now));
      playSound('status_update');

      // Web Push / Browser System Notification
      sendSystemNotification(
        `🎁 You're Near ${restaurant.value.name} (${dist.toFixed(1)} km)!`,
        `You are within 5 km! Visit us today to spin the Lucky Wheel & claim exclusive discounts!`,
        restaurant.value.logo_url || '/icon-192.png'
      );
    }
  }
};

const requestUserLocation = async () => {
  if (typeof navigator === 'undefined' || !navigator.geolocation) {
    locationChecked.value = true;
    return;
  }
  await requestNotificationPermission();

  navigator.geolocation.getCurrentPosition(
    (pos) => {
      locationChecked.value = true;
      userLocation.value = {
        lat: pos.coords.latitude,
        lng: pos.coords.longitude,
      };
      checkGeofenceNotification(pos.coords.latitude, pos.coords.longitude);

      if (!geofenceWatchId) {
        geofenceWatchId = navigator.geolocation.watchPosition(
          (p) => {
            userLocation.value = {
              lat: p.coords.latitude,
              lng: p.coords.longitude,
            };
            checkGeofenceNotification(p.coords.latitude, p.coords.longitude);
          },
          () => {},
          { enableHighAccuracy: true, maximumAge: 30000, timeout: 27000 }
        );
      }
    },
    (err) => {
      locationChecked.value = true;
      console.log('Location permission denied or unavailable', err);
    },
    { enableHighAccuracy: true, timeout: 8000 }
  );
};

onMounted(() => {
  fetchMenu();

  // Auto-detect if location permission is already granted by browser
  if (typeof navigator !== 'undefined' && 'permissions' in navigator && navigator.permissions.query) {
    navigator.permissions.query({ name: 'geolocation' }).then((result) => {
      if (result.state === 'granted') {
        requestUserLocation();
      }
    }).catch(() => {});
  }
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
