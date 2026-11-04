<template>
  <div class="min-h-screen bg-slate-900 flex flex-col justify-center py-12 sm:px-6 lg:px-8 text-white px-4">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center">
      <router-link to="/" class="inline-flex items-center gap-2 mb-4">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
          <i class="fa-solid fa-qrcode text-xl text-white"></i>
        </div>
        <span class="text-2xl font-extrabold text-white">QRMenu</span>
      </router-link>
      <h2 class="text-2xl font-bold tracking-tight text-white">Hotel / Restaurant Owner Login</h2>
      <p class="mt-1 text-xs text-slate-400">Manage your menu, view live orders and setup payments</p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-slate-800 py-8 px-6 shadow-2xl rounded-3xl sm:px-10 border border-slate-700/80">
        <!-- Quick Fill Helper Buttons -->
        <div class="mb-5 p-3 bg-slate-900/80 rounded-2xl border border-slate-700/60 text-xs">
          <span class="text-slate-400 font-semibold block mb-2">⚡ Quick Test Accounts:</span>
          <div class="grid grid-cols-2 gap-2">
            <button
              @click="fillCreds('rajesh@royalspice.com', 'password123')"
              type="button"
              class="p-2 bg-slate-800 hover:bg-slate-700 text-amber-300 font-medium rounded-xl border border-amber-500/30 text-left transition"
            >
              <div class="font-bold">Royal Spice</div>
              <div class="text-[10px] text-slate-400">rajesh@royalspice.com</div>
            </button>

            <button
              @click="fillCreds('pooja@greengarden.com', 'password123')"
              type="button"
              class="p-2 bg-slate-800 hover:bg-slate-700 text-amber-300 font-medium rounded-xl border border-amber-500/30 text-left transition"
            >
              <div class="font-bold">Green Garden</div>
              <div class="text-[10px] text-slate-400">pooja@greengarden.com</div>
            </button>
          </div>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div v-if="errorMessage" class="p-3 bg-rose-500/20 border border-rose-500/50 rounded-xl text-rose-300 text-xs">
            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ errorMessage }}
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Email Address</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="owner@restaurant.com"
              class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="••••••••"
              class="w-full px-3.5 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-amber-500/40"
            />
          </div>

          <button
            type="submit"
            :disabled="loading"
            class="w-full py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2 disabled:opacity-50 mt-2"
          >
            <i v-if="loading" class="fa-solid fa-circle-notch fa-spin"></i>
            <span v-else>Sign In to Dashboard</span>
          </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-700/60 text-center text-xs text-slate-400">
          Don't have an account yet?
          <router-link to="/owner/register" class="text-amber-400 hover:underline font-bold ml-1">
            Register your Restaurant
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
  email: '',
  password: '',
});

const loading = ref(false);
const errorMessage = ref('');

const fillCreds = (email, pass) => {
  form.value.email = email;
  form.value.password = pass;
};

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const res = await axios.post('/auth/login', form.value);
    if (res.data.success) {
      router.push('/owner/dashboard');
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Invalid email or password.';
  } finally {
    loading.value = false;
  }
};
</script>
