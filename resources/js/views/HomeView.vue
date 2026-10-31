<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white">
    <!-- Navbar -->
    <header class="border-b border-slate-700/60 backdrop-blur-md bg-slate-900/60 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
            <i class="fa-solid fa-qrcode text-xl text-white"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
              QRMenu <span class="text-xs bg-amber-500/20 text-amber-300 font-semibold px-2 py-0.5 rounded-full border border-amber-500/30">Multi-Restaurant</span>
            </h1>
          </div>
        </div>

        <div class="flex items-center space-x-3">
          <router-link
            to="/owner/login"
            class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 rounded-lg transition"
          >
            <i class="fa-solid fa-arrow-right-to-bracket mr-1.5"></i> Owner Login
          </router-link>
          <router-link
            to="/owner/register"
            class="px-4 py-2 text-sm font-semibold text-slate-900 bg-amber-400 hover:bg-amber-300 rounded-lg shadow-md shadow-amber-400/20 transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-store"></i> Register Restaurant
          </router-link>
        </div>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
      <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold mb-6">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Next-Gen QR Food Ordering & Payment System
        </div>
        <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight leading-tight mb-6">
          Smart QR Menu & Direct Payment for
          <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400 bg-clip-text text-transparent">Hotels & Restaurants</span>
        </h2>
        <p class="text-lg text-slate-300 mb-8 leading-relaxed">
          Har hotel aur restaurant ka alag menu, table-wise QR codes, instant customer order placement, aur UPI/Direct online payment setup — sab ek click me!
        </p>

        <!-- Quick Demo Accounts Box -->
        <div class="bg-slate-800/80 border border-amber-500/40 rounded-2xl p-5 text-left mb-8 backdrop-blur shadow-2xl">
          <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-bold text-amber-400 flex items-center gap-2">
              <i class="fa-solid fa-key"></i> Pre-Configured Demo Business Owner Accounts:
            </h3>
            <span class="text-xs text-slate-400 bg-slate-900 px-2 py-0.5 rounded">Quick Test</span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
            <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-700/60">
              <div class="font-bold text-white text-sm mb-1 flex items-center justify-between">
                <span>🏨 Royal Spice Palace</span>
                <span class="text-[9px] bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded font-mono">Restaurant</span>
              </div>
              <div class="text-slate-300">Email: <span class="font-mono text-amber-300">rajesh@royalspice.com</span></div>
              <div class="text-slate-300">Password: <span class="font-mono text-amber-300">password123</span></div>
              <div class="mt-2 flex gap-2">
                <router-link to="/owner/login" class="text-amber-400 hover:underline font-semibold">Login →</router-link>
                <router-link to="/r/royal-spice" class="text-slate-400 hover:text-white">| Menu →</router-link>
              </div>
            </div>

            <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-700/60">
              <div class="font-bold text-white text-sm mb-1 flex items-center justify-between">
                <span>☕ Green Garden Bistro</span>
                <span class="text-[9px] bg-emerald-500/20 text-emerald-300 px-1.5 py-0.5 rounded font-mono">Cafe</span>
              </div>
              <div class="text-slate-300">Email: <span class="font-mono text-amber-300">pooja@greengarden.com</span></div>
              <div class="text-slate-300">Password: <span class="font-mono text-amber-300">password123</span></div>
              <div class="mt-2 flex gap-2">
                <router-link to="/owner/login" class="text-amber-400 hover:underline font-semibold">Login →</router-link>
                <router-link to="/r/green-garden" class="text-slate-400 hover:text-white">| Menu →</router-link>
              </div>
            </div>

            <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-700/60">
              <div class="font-bold text-white text-sm mb-1 flex items-center justify-between">
                <span>💈 Royal Scissor Lounge</span>
                <span class="text-[9px] bg-cyan-500/20 text-cyan-300 px-1.5 py-0.5 rounded font-mono">Salon</span>
              </div>
              <div class="text-slate-300">Email: <span class="font-mono text-cyan-300">anil@royalscissors.com</span></div>
              <div class="text-slate-300">Password: <span class="font-mono text-cyan-300">password123</span></div>
              <div class="mt-2 flex gap-2">
                <router-link to="/owner/login" class="text-cyan-400 hover:underline font-semibold">Login →</router-link>
                <router-link to="/r/royal-scissors" class="text-slate-400 hover:text-white">| Book Slot →</router-link>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4">
          <router-link
            to="/owner/register"
            class="px-6 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl shadow-lg shadow-orange-500/25 transition flex items-center gap-2"
          >
            <i class="fa-solid fa-plus-circle"></i> Create Your Restaurant Menu
          </router-link>
          <a
            href="#live-restaurants"
            class="px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-medium rounded-xl border border-slate-700 transition flex items-center gap-2"
          >
            <i class="fa-solid fa-utensils"></i> Browse Active Restaurants
          </a>
        </div>
      </div>

      <!-- Feature Badges -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-20">
        <div class="p-5 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-center">
          <div class="w-12 h-12 mx-auto rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mb-3">
            <i class="fa-solid fa-building"></i>
          </div>
          <h4 class="font-bold text-white mb-1">Multi-Restaurant</h4>
          <p class="text-xs text-slate-400">Har owner ka apna isolated menu, rates, aur table settings</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-center">
          <div class="w-12 h-12 mx-auto rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center text-xl mb-3">
            <i class="fa-solid fa-qrcode"></i>
          </div>
          <h4 class="font-bold text-white mb-1">Table QR Scanning</h4>
          <p class="text-xs text-slate-400">Customer scan karega aur table number automatically detect ho jayega</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-center">
          <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl mb-3">
            <i class="fa-solid fa-indian-rupee-sign"></i>
          </div>
          <h4 class="font-bold text-white mb-1">UPI & Online Payment</h4>
          <p class="text-xs text-slate-400">Owner ka apna UPI ID / QR set up aur customer direct pay karega</p>
        </div>
        <div class="p-5 rounded-2xl bg-slate-800/40 border border-slate-700/60 text-center">
          <div class="w-12 h-12 mx-auto rounded-xl bg-purple-500/10 text-purple-400 flex items-center justify-center text-xl mb-3">
            <i class="fa-solid fa-bell-concierge"></i>
          </div>
          <h4 class="font-bold text-white mb-1">Live Order Management</h4>
          <p class="text-xs text-slate-400">Kitchen screen pe live orders, status update (Preparing/Ready/Served)</p>
        </div>
      </div>

      <!-- Active Restaurants Section -->
      <div id="live-restaurants" class="pt-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
          <div>
            <h3 class="text-2xl font-bold text-white">Explore Businesses & Services</h3>
            <p class="text-sm text-slate-400">Select any restaurant, salon, or store to test the customer ordering & slot booking experience</p>
          </div>
        </div>

        <!-- Category Filter Tabs -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 no-scrollbar">
          <button
            v-for="cat in [
              { id: 'all', name: 'All Businesses', icon: 'fa-solid fa-shapes' },
              { id: 'food', name: 'Restaurants & Food', icon: 'fa-solid fa-utensils' },
              { id: 'salon', name: 'Barbershops & Salons', icon: 'fa-solid fa-scissors' },
              { id: 'retail', name: 'Clothing & Retail', icon: 'fa-solid fa-shirt' }
            ]"
            :key="cat.id"
            @click="businessFilter = cat.id"
            :class="[
              'px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-2 whitespace-nowrap cursor-pointer',
              businessFilter === cat.id
                ? 'bg-amber-500 text-slate-950 shadow-md shadow-amber-500/20'
                : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700'
            ]"
          >
            <i :class="cat.icon"></i>
            <span>{{ cat.name }}</span>
          </button>
        </div>

        <div v-if="loading" class="text-center py-12">
          <i class="fa-solid fa-circle-notch fa-spin text-3xl text-amber-400"></i>
          <p class="text-slate-400 text-sm mt-2">Loading businesses...</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div
            v-for="rest in filteredRestaurants"
            :key="rest.id"
            class="bg-slate-800/90 rounded-2xl border border-slate-700/80 overflow-hidden hover:border-amber-500/50 transition duration-300 shadow-xl group flex flex-col"
          >
            <div class="h-44 relative overflow-hidden bg-slate-900">
              <img
                :src="rest.cover_url || 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=800&fit=crop'"
                :alt="rest.name"
                class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
              />
              <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-transparent to-transparent"></div>

              <!-- Badges on top -->
              <div class="absolute top-3 left-3">
                <span
                  v-if="rest.business_type === 'salon'"
                  class="bg-cyan-500/90 text-slate-950 font-extrabold text-[10px] px-2.5 py-1 rounded-full shadow flex items-center gap-1"
                >
                  <i class="fa-solid fa-scissors text-[9px]"></i> Barber & Salon
                </span>
                <span
                  v-else-if="rest.business_type === 'retail'"
                  class="bg-emerald-500/90 text-slate-950 font-extrabold text-[10px] px-2.5 py-1 rounded-full shadow flex items-center gap-1"
                >
                  <i class="fa-solid fa-shirt text-[9px]"></i> Clothing & Retail
                </span>
                <span
                  v-else
                  class="bg-amber-500/90 text-slate-950 font-extrabold text-[10px] px-2.5 py-1 rounded-full shadow flex items-center gap-1"
                >
                  <i class="fa-solid fa-utensils text-[9px]"></i> Food & Dining
                </span>
              </div>

              <div class="absolute top-3 right-3 bg-slate-900/80 backdrop-blur px-2.5 py-1 rounded-full text-xs font-semibold text-emerald-400 border border-emerald-500/30">
                <i class="fa-solid fa-circle text-[8px] mr-1 animate-pulse"></i> Open
              </div>

              <div class="absolute bottom-3 left-3 flex items-center space-x-2">
                <img
                  :src="rest.logo_url || 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=100&fit=crop'"
                  class="w-10 h-10 rounded-xl object-cover border-2 border-white/80 shadow"
                />
                <div>
                  <h4 class="font-bold text-white text-base leading-tight">{{ rest.name }}</h4>
                  <p class="text-xs text-slate-300">{{ rest.address }}</p>
                </div>
              </div>
            </div>

            <div class="p-5 flex-1 flex flex-col justify-between">
              <div>
                <p class="text-xs text-amber-300 font-semibold mb-2">
                  <i class="fa-solid fa-quote-left text-xs mr-1 opacity-70"></i> {{ rest.tagline }}
                </p>
                <p class="text-xs text-slate-400 line-clamp-2 mb-4">{{ rest.description }}</p>

                <div class="grid grid-cols-2 gap-2 text-[11px] text-slate-300 mb-4 bg-slate-900/50 p-2.5 rounded-xl border border-slate-700/50">
                  <div>
                    <span class="text-slate-400 block">{{ rest.business_type === 'salon' ? 'Chairs / Stylists:' : 'Tables:' }}</span>
                    <span class="font-semibold text-white">{{ rest.table_count }} {{ rest.business_type === 'salon' ? 'Chairs' : 'Tables' }}</span>
                  </div>
                  <div>
                    <span class="text-slate-400 block">{{ rest.business_type === 'salon' ? 'Slot Duration:' : 'Payment:' }}</span>
                    <span class="font-semibold text-amber-300">
                      {{ rest.business_type === 'salon' ? (rest.slot_duration_minutes || 30) + ' Mins' : (rest.upi_id ? 'UPI + Cash' : 'Cash on Counter') }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="space-y-2 pt-2">
                <!-- Action Button -->
                <router-link
                  v-if="rest.business_type === 'salon'"
                  :to="`/r/${rest.slug}`"
                  class="w-full py-2.5 px-4 bg-cyan-500 hover:bg-cyan-400 text-slate-950 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow"
                >
                  <i class="fa-solid fa-calendar-check"></i> Book Haircut Slot (No Waiting)
                </router-link>
                <router-link
                  v-else
                  :to="`/r/${rest.slug}?table=1`"
                  class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm rounded-xl transition flex items-center justify-center gap-2 shadow"
                >
                  <i class="fa-solid fa-qrcode"></i> Scan / Open Menu (Table 1)
                </router-link>

                <!-- Direct URL preview -->
                <div class="text-[11px] text-center text-slate-400">
                  Slug: <span class="font-mono text-amber-400">/r/{{ rest.slug }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-950/80 py-8 mt-auto">
      <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500">
        QRMenu Multi-Restaurant SaaS System &copy; 2026. Built with Laravel 11, Vue 3, Tailwind CSS & MySQL.
      </div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const restaurants = ref([]);
const loading = ref(true);
const businessFilter = ref('all');

const filteredRestaurants = computed(() => {
  if (businessFilter.value === 'all') return restaurants.value;
  return restaurants.value.filter(r => (r.business_type || 'food') === businessFilter.value);
});

const fetchRestaurants = async () => {
  try {
    const res = await axios.get('/restaurants');
    if (res.data.success) {
      restaurants.value = res.data.restaurants;
    }
  } catch (err) {
    console.error('Failed to load restaurants', err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchRestaurants();
});
</script>
