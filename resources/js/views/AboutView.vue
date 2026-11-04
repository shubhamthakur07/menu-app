<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-950 text-white flex flex-col">
    <!-- Navbar -->
    <header class="border-b border-slate-700/60 backdrop-blur-md bg-slate-900/70 sticky top-0 z-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
        <router-link to="/" class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
            <i class="fa-solid fa-qrcode text-xl text-white"></i>
          </div>
          <div>
            <h1 class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
              QRMenu <span class="text-xs bg-amber-500/20 text-amber-300 font-semibold px-2 py-0.5 rounded-full border border-amber-500/30">About Us</span>
            </h1>
          </div>
        </router-link>

        <div class="flex items-center space-x-3 text-sm">
          <router-link
            to="/"
            class="px-3.5 py-1.5 font-medium text-slate-300 hover:text-white hover:bg-slate-800/80 rounded-lg transition hidden sm:inline-flex items-center gap-1.5"
          >
            <i class="fa-solid fa-house text-xs"></i> Home
          </router-link>
          <router-link
            to="/owner/login"
            class="px-3.5 py-1.5 font-medium text-slate-300 hover:text-white hover:bg-slate-800/80 rounded-lg transition inline-flex items-center gap-1.5"
          >
            <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i> Login
          </router-link>
          <router-link
            to="/owner/register"
            class="px-3.5 py-1.5 font-semibold text-slate-950 bg-amber-400 hover:bg-amber-300 rounded-lg shadow-md shadow-amber-400/20 transition flex items-center gap-1.5 text-xs sm:text-sm"
          >
            <i class="fa-solid fa-store"></i> Register
          </router-link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
      <!-- Hero Intro -->
      <div class="text-center max-w-3xl mx-auto mb-16">
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs font-semibold mb-5">
          <i class="fa-solid fa-shield-halved"></i> Transforming Restaurant Dining & Contactless Ordering
        </div>
        <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-tight mb-4">
          About <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-rose-400 bg-clip-text text-transparent">QRMenu Platform</span>
        </h1>
        <p class="text-base sm:text-lg text-slate-300 leading-relaxed">
          QRMenu is an advanced, ultra-fast Digital Menu & Real-time Restaurant Management system built to eliminate paper menus, reduce customer waiting times, and maximize restaurant profitability with 0% commissions.
        </p>
      </div>

      <!-- Founder & Developer Card (Highlighted) -->
      <section class="mb-16">
        <div class="relative bg-gradient-to-b from-slate-800 to-slate-900/90 rounded-3xl p-6 sm:p-10 border-2 border-amber-500/40 shadow-2xl overflow-hidden backdrop-blur">
          <!-- Background Glow Accent -->
          <div class="absolute -right-20 -top-20 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
          <div class="absolute -left-20 -bottom-20 w-80 h-80 bg-orange-500/10 rounded-full blur-3xl pointer-events-none"></div>

          <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
            <!-- Left: Avatar / Bio Info -->
            <div class="lg:col-span-5 text-center lg:text-left flex flex-col items-center lg:items-start">
              <div class="relative mb-4">
                <div class="w-28 h-28 sm:w-32 sm:h-32 rounded-3xl bg-gradient-to-tr from-amber-500 via-orange-500 to-rose-500 p-1 shadow-2xl shadow-orange-500/30">
                  <div class="w-full h-full bg-slate-900 rounded-[22px] flex items-center justify-center text-4xl text-amber-400 font-black">
                    ST
                  </div>
                </div>
                <div class="absolute -bottom-2 -right-2 bg-emerald-500 text-slate-950 text-[10px] font-black uppercase px-2 py-0.5 rounded-full border-2 border-slate-900 shadow flex items-center gap-1">
                  <span class="w-1.5 h-1.5 rounded-full bg-slate-950 animate-ping"></span>
                  Founder
                </div>
              </div>

              <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Shubham Thakur</h2>
              <p class="text-amber-400 font-semibold text-sm mt-0.5">Creator & Lead Developer</p>
              <p class="text-xs text-slate-400 mt-2 leading-relaxed max-w-sm">
                Passionate Full-Stack Developer specializing in high-performance web systems, real-time architectures, and SaaS applications for modern businesses.
              </p>

              <!-- Quick Badges -->
              <div class="flex flex-wrap gap-2 mt-4 justify-center lg:justify-start">
                <span class="px-2.5 py-1 bg-slate-800 text-slate-300 text-[11px] font-medium rounded-lg border border-slate-700">
                  <i class="fa-brands fa-laravel text-red-400 mr-1"></i> Laravel
                </span>
                <span class="px-2.5 py-1 bg-slate-800 text-slate-300 text-[11px] font-medium rounded-lg border border-slate-700">
                  <i class="fa-brands fa-vuejs text-emerald-400 mr-1"></i> Vue 3
                </span>
                <span class="px-2.5 py-1 bg-slate-800 text-slate-300 text-[11px] font-medium rounded-lg border border-slate-700">
                  <i class="fa-solid fa-bolt text-amber-400 mr-1"></i> Real-time UI
                </span>
              </div>
            </div>

            <!-- Right: Direct Contact & Action Card -->
            <div class="lg:col-span-7 bg-slate-900/80 rounded-2xl p-6 sm:p-8 border border-slate-700/80 shadow-inner">
              <h3 class="text-lg font-bold text-white mb-2 flex items-center gap-2">
                <i class="fa-solid fa-address-book text-amber-400"></i>
                Get in Touch Directly / Business Inquiries
              </h3>
              <p class="text-xs text-slate-400 mb-6 leading-relaxed">
                Want to implement this QR ordering system in your hotel, restaurant, cafe, or salon? Have custom feature requirements or partnership inquiries? Feel free to call or WhatsApp directly!
              </p>

              <div class="space-y-4 mb-6">
                <!-- Phone Box -->
                <div class="flex items-center justify-between p-3.5 bg-slate-800/90 rounded-xl border border-slate-700/80">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-500/20">
                      <i class="fa-solid fa-phone"></i>
                    </div>
                    <div>
                      <div class="text-[11px] text-slate-400 font-medium">Direct Phone / Call</div>
                      <div class="text-sm sm:text-base font-bold text-white tracking-wide font-mono">+91 8219795479</div>
                    </div>
                  </div>
                  <a
                    href="tel:+918219795479"
                    class="px-3.5 py-2 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs rounded-lg transition flex items-center gap-1.5 shadow"
                  >
                    <i class="fa-solid fa-phone"></i> Call Now
                  </a>
                </div>

                <!-- WhatsApp Box -->
                <div class="flex items-center justify-between p-3.5 bg-slate-800/90 rounded-xl border border-slate-700/80">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-lg shrink-0 border border-emerald-500/20">
                      <i class="fa-brands fa-whatsapp text-xl"></i>
                    </div>
                    <div>
                      <div class="text-[11px] text-slate-400 font-medium">WhatsApp Support</div>
                      <div class="text-sm sm:text-base font-bold text-white tracking-wide font-mono">8219795479</div>
                    </div>
                  </div>
                  <a
                    href="https://wa.me/918219795479?text=Hi%20Shubham,%20I%20am%20interested%20in%20the%20QRMenu%20app%20for%20my%20restaurant."
                    target="_blank"
                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-lg transition flex items-center gap-1.5 shadow"
                  >
                    <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
                  </a>
                </div>
              </div>

              <!-- Quick Links Footer -->
              <div class="pt-4 border-t border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                <span class="flex items-center gap-1.5">
                  <i class="fa-solid fa-location-dot text-amber-400"></i> Available for setups across India
                </span>
                <a
                  href="/guide.html"
                  target="_blank"
                  class="text-amber-400 hover:text-amber-300 font-semibold inline-flex items-center gap-1"
                >
                  <i class="fa-solid fa-file-pdf"></i> View Staff & User Manual →
                </a>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Key Platform Pillars -->
      <section class="mb-16">
        <div class="text-center mb-10">
          <h2 class="text-2xl sm:text-3xl font-bold text-white">Why We Built QRMenu</h2>
          <p class="text-xs sm:text-sm text-slate-400 mt-2">Solving real day-to-day challenges faced by restaurant owners and diners</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shadow-lg">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center text-xl mb-4 border border-amber-500/20">
              <i class="fa-solid fa-hand-holding-dollar"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">0% Platform Commissions</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
              Unlike traditional food delivery aggregators that take 25% to 35% cut on every dish, QRMenu puts 100% of the revenue directly into the restaurant owner's UPI bank account.
            </p>
          </div>

          <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shadow-lg">
            <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center text-xl mb-4 border border-emerald-500/20">
              <i class="fa-solid fa-bell"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Live Audio & Push Alerts</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
              Equipped with Web Audio API synthesizer chimes and background polling. Chefs and cashiers hear live bells instantly when an order arrives or payment completes.
            </p>
          </div>

          <div class="bg-slate-800/80 p-6 rounded-2xl border border-slate-700/80 shadow-lg">
            <div class="w-12 h-12 rounded-xl bg-cyan-500/10 text-cyan-400 flex items-center justify-center text-xl mb-4 border border-cyan-500/20">
              <i class="fa-solid fa-gamepad"></i>
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Interactive 2-Game Tournament</h3>
            <p class="text-xs text-slate-300 leading-relaxed">
              Keeps waiting customers delightfully engaged with games on their phone while their food is prepared in the kitchen, offering instant 5% dining discounts!
            </p>
          </div>
        </div>
      </section>

      <!-- System Features Summary -->
      <section class="bg-slate-800/50 rounded-3xl p-8 border border-slate-700/60 mb-16">
        <h3 class="text-xl font-bold text-white mb-6 text-center">Comprehensive Operational Features</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 text-xs">
          <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/50">
            <div class="text-amber-400 font-bold text-sm mb-1">
              <i class="fa-solid fa-qrcode mr-1"></i> Table QR Standees
            </div>
            <p class="text-slate-400 leading-relaxed">Download and print professional PDF QR codes with your restaurant branding.</p>
          </div>

          <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/50">
            <div class="text-emerald-400 font-bold text-sm mb-1">
              <i class="fa-solid fa-cubes-stacked mr-1"></i> Table Occupancy
            </div>
            <p class="text-slate-400 leading-relaxed">Smart detection prevents overlapping orders on already occupied tables.</p>
          </div>

          <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/50">
            <div class="text-cyan-400 font-bold text-sm mb-1">
              <i class="fa-solid fa-truck mr-1"></i> Delivery Toggle
            </div>
            <p class="text-slate-400 leading-relaxed">Owners can easily switch Home Delivery ON or OFF based on staff availability.</p>
          </div>

          <div class="p-4 bg-slate-900/60 rounded-xl border border-slate-700/50">
            <div class="text-rose-400 font-bold text-sm mb-1">
              <i class="fa-solid fa-rotate mr-1"></i> + Order More Items
            </div>
            <p class="text-slate-400 leading-relaxed">Customers can re-order mid-meal without having to re-enter their details.</p>
          </div>
        </div>
      </section>

      <!-- CTA Bottom Banner -->
      <div class="text-center bg-gradient-to-r from-amber-500/20 via-orange-500/20 to-rose-500/20 border border-amber-500/30 rounded-3xl p-8 sm:p-12">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">Ready to digitize your restaurant?</h2>
        <p class="text-xs sm:text-sm text-slate-300 max-w-xl mx-auto mb-6">
          Register your hotel or restaurant today in under 2 minutes and start accepting QR orders immediately.
        </p>
        <div class="flex flex-wrap items-center justify-center gap-4">
          <router-link
            to="/owner/register"
            class="px-6 py-3 bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-sm rounded-xl transition shadow-lg shadow-amber-400/20 flex items-center gap-2"
          >
            <i class="fa-solid fa-store"></i> Register Your Restaurant
          </router-link>
          <a
            href="tel:+918219795479"
            class="px-6 py-3 bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm rounded-xl border border-slate-600 transition flex items-center gap-2"
          >
            <i class="fa-solid fa-phone text-emerald-400"></i> Contact Shubham: 8219795479
          </a>
        </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 bg-slate-950/80 py-8 mt-auto">
      <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
        <div>
          QRMenu SaaS Platform &copy; 2026. Built by <span class="text-slate-300 font-semibold">Shubham Thakur</span>.
        </div>
        <div class="flex items-center space-x-4">
          <router-link to="/" class="hover:text-slate-300 transition">Home</router-link>
          <router-link to="/about" class="text-amber-400 font-semibold hover:underline">About Us</router-link>
          <a href="/guide.html" target="_blank" class="hover:text-slate-300 transition">Staff Guide (PDF)</a>
          <router-link to="/owner/login" class="hover:text-slate-300 transition">Login</router-link>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup>
// Pure presentation component with interactive actions
</script>
