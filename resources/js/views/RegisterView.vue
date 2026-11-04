<template>
  <div class="min-h-screen bg-slate-900 py-12 px-4 sm:px-6 lg:px-8 text-white flex flex-col justify-center">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg text-center">
      <router-link to="/" class="inline-flex items-center gap-2 mb-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
          <i class="fa-solid fa-store text-xl text-white"></i>
        </div>
        <span class="text-2xl font-extrabold text-white">QRMenu</span>
      </router-link>
      <h2 class="text-2xl font-bold tracking-tight text-white">Create Restaurant / Hotel Owner Account</h2>
      <p class="mt-1 text-xs text-slate-400">Register your business and generate instant QR Menus</p>
    </div>

    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-lg">
      <div class="bg-slate-800 py-8 px-6 shadow-2xl rounded-3xl sm:px-10 border border-slate-700/80">
        <form @submit.prevent="handleRegister" class="space-y-4">
          <div v-if="errorMessage" class="p-3 bg-rose-500/20 border border-rose-500/50 rounded-xl text-rose-300 text-xs">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ errorMessage }}
          </div>

          <!-- Section 1: Owner Info -->
          <div>
            <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <i class="fa-solid fa-user"></i> Owner Profile
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Your Full Name *</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  placeholder="e.g. Vikram Singh"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Phone Number</label>
                <input
                  v-model="form.phone"
                  type="tel"
                  placeholder="+91 9876543210"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Email (Username) *</label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  placeholder="vikram@restaurant.com"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Password *</label>
                <input
                  v-model="form.password"
                  type="password"
                  required
                  placeholder="Min 6 characters"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>
            </div>
          </div>

          <!-- Section 2: Business & Payment Setup -->
          <div class="pt-3 border-t border-slate-700/60">
            <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
              <i class="fa-solid fa-briefcase"></i> Business Category & Setup
            </h3>

            <!-- Business Category Picker -->
            <div class="mb-3">
              <label class="block text-xs font-semibold text-slate-300 mb-1.5">Choose Business Type *</label>
              <div class="grid grid-cols-3 gap-2">
                <label
                  :class="[
                    'p-2.5 rounded-xl border cursor-pointer text-center transition flex flex-col items-center justify-center gap-1',
                    form.business_type === 'food' ? 'bg-amber-500/20 border-amber-500 text-white ring-1 ring-amber-500/30' : 'bg-slate-900 border-slate-700 text-slate-400 hover:text-white'
                  ]"
                >
                  <input type="radio" value="food" v-model="form.business_type" class="hidden" />
                  <i class="fa-solid fa-utensils text-base text-amber-400"></i>
                  <span class="text-[11px] font-bold block">Food & Dining</span>
                  <span class="text-[9px] text-slate-400">Table QR & Menu</span>
                </label>

                <label
                  :class="[
                    'p-2.5 rounded-xl border cursor-pointer text-center transition flex flex-col items-center justify-center gap-1',
                    form.business_type === 'salon' ? 'bg-amber-500/20 border-amber-500 text-white ring-1 ring-amber-500/30' : 'bg-slate-900 border-slate-700 text-slate-400 hover:text-white'
                  ]"
                >
                  <input type="radio" value="salon" v-model="form.business_type" class="hidden" />
                  <i class="fa-solid fa-scissors text-base text-cyan-400"></i>
                  <span class="text-[11px] font-bold block">Barber & Salon</span>
                  <span class="text-[9px] text-slate-400">Slots & Queue Token</span>
                </label>

                <label
                  :class="[
                    'p-2.5 rounded-xl border cursor-pointer text-center transition flex flex-col items-center justify-center gap-1',
                    form.business_type === 'retail' ? 'bg-amber-500/20 border-amber-500 text-white ring-1 ring-amber-500/30' : 'bg-slate-900 border-slate-700 text-slate-400 hover:text-white'
                  ]"
                >
                  <input type="radio" value="retail" v-model="form.business_type" class="hidden" />
                  <i class="fa-solid fa-shirt text-base text-emerald-400"></i>
                  <span class="text-[11px] font-bold block">Clothing & Retail</span>
                  <span class="text-[9px] text-slate-400">Catalog & Pickup</span>
                </label>
              </div>
            </div>

            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">
                  {{ form.business_type === 'salon' ? 'Barbershop / Salon Name *' : (form.business_type === 'retail' ? 'Store / Boutique Name *' : 'Restaurant / Hotel Name *') }}
                </label>
                <input
                  v-model="form.restaurant_name"
                  type="text"
                  required
                  :placeholder="form.business_type === 'salon' ? 'e.g. Royal Scissor Barbershop & Spa' : (form.business_type === 'retail' ? 'e.g. Urban Style Clothing' : 'e.g. Saffron Cloud Kitchen & Restro')"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">Shop / Outlet Address</label>
                <input
                  v-model="form.restaurant_address"
                  type="text"
                  placeholder="e.g. Shop 4, High Street Market, Jaipur"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">
                  UPI ID for Customer Online Payments (Optional)
                  <span class="text-[10px] text-amber-300 font-normal">(Customers pay directly to this UPI)</span>
                </label>
                <input
                  v-model="form.upi_id"
                  type="text"
                  placeholder="e.g. saffronhotel@okhdfcbank"
                  class="w-full px-3 py-2 bg-slate-900 border border-slate-700 rounded-xl text-xs text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
                />
              </div>
            </div>
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50 mt-4"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch fa-spin"></i>
            <span v-else>Register & Launch QR Menu</span>
          </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-700/60 text-center text-xs text-slate-400">
          Already registered?
          <router-link to="/owner/login" class="text-amber-400 hover:underline font-bold ml-1">
            Sign In here
          </router-link>
        </div>
      </div>

      <div class="mt-6 text-center text-xs text-slate-500 flex items-center justify-center gap-3">
        <router-link to="/" class="hover:text-slate-300 transition">← Back to Home</router-link>
        <span>•</span>
        <router-link to="/about" class="text-amber-400/90 hover:underline">About Us & Founder Info</router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();

const form = ref({
  name: '',
  email: '',
  password: '',
  phone: '',
  business_type: 'food',
  restaurant_name: '',
  restaurant_address: '',
  upi_id: '',
});

const loading = ref(false);
const errorMessage = ref('');

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const res = await axios.post('/auth/register', form.value);
    if (res.data.success) {
      router.push('/owner/dashboard');
    }
  } catch (err) {
    if (err.response?.data?.errors) {
      const firstError = Object.values(err.response.data.errors)[0];
      errorMessage.value = Array.isArray(firstError) ? firstError[0] : firstError;
    } else {
      errorMessage.value = err.response?.data?.message || 'Registration failed.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
