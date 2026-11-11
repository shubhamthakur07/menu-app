<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 flex flex-col font-sans">
    <!-- Super Admin Header -->
    <header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-30 shadow-md">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-slate-950 font-black shadow-md">
            <i class="fa-solid fa-crown text-base"></i>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="font-extrabold text-white text-base tracking-tight">QRMenu Super Admin</span>
              <span class="text-[10px] bg-amber-500/20 text-amber-300 font-extrabold px-2 py-0.5 rounded-full border border-amber-500/40">MASTER CONTROL</span>
            </div>
            <p class="text-[11px] text-slate-400">Plan Customizer & Client Subscription Manager</p>
          </div>
        </div>

        <div class="flex items-center gap-3">
          <router-link
            to="/owner/dashboard"
            class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl text-xs font-bold transition flex items-center gap-1.5"
          >
            <i class="fa-solid fa-arrow-left"></i>
            <span>Owner Dashboard</span>
          </router-link>
        </div>
      </div>

      <!-- Navigation Tabs -->
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex space-x-2 border-t border-slate-800/80 pt-1">
        <button
          @click="activeAdminTab = 'plans'"
          :class="[
            'px-4 py-2 text-xs font-bold rounded-t-xl transition flex items-center gap-2 border-b-2',
            activeAdminTab === 'plans'
              ? 'border-amber-500 text-amber-400 bg-slate-800/60'
              : 'border-transparent text-slate-400 hover:text-white'
          ]"
        >
          <i class="fa-solid fa-sliders"></i>
          <span>Plans & Features Customizer</span>
        </button>

        <button
          @click="activeAdminTab = 'subscribers'"
          :class="[
            'px-4 py-2 text-xs font-bold rounded-t-xl transition flex items-center gap-2 border-b-2',
            activeAdminTab === 'subscribers'
              ? 'border-amber-500 text-amber-400 bg-slate-800/60'
              : 'border-transparent text-slate-400 hover:text-white'
          ]"
        >
          <i class="fa-solid fa-store"></i>
          <span>Subscribers & Restaurants ({{ subscribers.length }})</span>
        </button>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-20">
        <i class="fa-solid fa-circle-notch fa-spin text-4xl text-amber-500 mb-3"></i>
        <p class="text-slate-400 text-sm">Loading admin subscription controls...</p>
      </div>

      <div v-else>
        <!-- Notice Banner: Grandfathering Explanation -->
        <div class="mb-6 p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3 text-xs text-amber-200">
          <div class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
            <i class="fa-solid fa-shield-halved text-sm"></i>
          </div>
          <div>
            <div class="font-bold text-amber-300">Grandfathering & Subscription Cycle Protection Active:</div>
            <p class="text-[11px] text-amber-200/90 mt-0.5 leading-relaxed">
              Jab aap kisi plan ke features add/drop karte hain ya price badalte hain, toh <strong>purane subscribers ka current plan waise hi chalta rahega</strong>. Unhe naye features ya price tabhi apply honge jab unka next billing cycle renew hoga ya wo upgrade karenge.
            </p>
          </div>
        </div>

        <!-- TAB 1: PLANS & FEATURE CUSTOMIZER -->
        <div v-if="activeAdminTab === 'plans'" class="space-y-6">
          <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
              <h2 class="text-lg font-bold text-white">Subscription Plans & Feature Bundles</h2>
              <p class="text-xs text-slate-400">Har plan me features ko Add ya Drop karein aur monthly/yearly price set karein.</p>
            </div>
            <button
              @click="openNewPlanModal"
              class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black rounded-xl text-xs shadow-lg transition flex items-center gap-1.5 cursor-pointer"
            >
              <i class="fa-solid fa-plus"></i>
              <span>Create New Custom Plan</span>
            </button>
          </div>

          <!-- Plans Grid -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div
              v-for="plan in plans"
              :key="plan.id"
              class="bg-slate-900 border border-slate-800 rounded-3xl p-5 sm:p-6 shadow-xl flex flex-col justify-between relative hover:border-slate-700 transition"
            >
              <!-- Card Header -->
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span
                    v-if="plan.badge_text"
                    class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40"
                  >
                    {{ plan.badge_text }}
                  </span>
                  <span v-else class="text-[10px] text-slate-500 font-bold uppercase tracking-wider">Plan ID: #{{ plan.id }}</span>

                  <div class="flex items-center gap-1">
                    <button
                      @click="editPlan(plan)"
                      class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center text-xs cursor-pointer"
                      title="Edit Plan"
                    >
                      <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    <button
                      v-if="plans.length > 1"
                      @click="deletePlan(plan.id)"
                      class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-rose-900/60 text-slate-400 hover:text-rose-300 flex items-center justify-center text-xs cursor-pointer"
                      title="Delete Plan"
                    >
                      <i class="fa-solid fa-trash"></i>
                    </button>
                  </div>
                </div>

                <h3 class="text-xl font-extrabold text-white mt-1">{{ plan.name }}</h3>
                <p class="text-xs text-slate-400 mt-1 min-h-[32px]">{{ plan.description || 'Standard subscription tier' }}</p>

                <!-- Pricing Display -->
                <div class="mt-4 p-3 bg-slate-950/60 rounded-2xl border border-slate-800/80 flex items-baseline justify-between">
                  <div>
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Monthly</div>
                    <div class="text-xl font-black text-amber-400">₹{{ plan.price_monthly }}<span class="text-xs font-normal text-slate-400">/mo</span></div>
                  </div>
                  <div class="text-right">
                    <div class="text-[10px] text-slate-400 uppercase font-bold">Yearly (Save 30%)</div>
                    <div class="text-xl font-black text-emerald-400">₹{{ plan.price_yearly }}<span class="text-xs font-normal text-slate-400">/yr</span></div>
                  </div>
                </div>

                <!-- Feature Toggles Checklist -->
                <div class="mt-5 space-y-2.5">
                  <div class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Included Features:</div>
                  <div
                    v-for="(def, key) in featureDefinitions"
                    :key="key"
                    class="flex items-center justify-between text-xs py-1.5 border-b border-slate-800/50"
                  >
                    <span :class="plan.features && plan.features[key] ? 'text-slate-200 font-semibold' : 'text-slate-500 line-through'">
                      {{ def.label }}
                    </span>
                    <span
                      :class="plan.features && plan.features[key] ? 'text-emerald-400' : 'text-slate-600'"
                      class="font-bold text-sm"
                    >
                      <i :class="plan.features && plan.features[key] ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-xmark'"></i>
                    </span>
                  </div>
                </div>
              </div>

              <!-- Footer Edit Trigger -->
              <div class="mt-6 pt-4 border-t border-slate-800">
                <button
                  @click="editPlan(plan)"
                  class="w-full py-2.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition flex items-center justify-center gap-1.5 cursor-pointer"
                >
                  <i class="fa-solid fa-sliders text-amber-400"></i>
                  <span>Customize Features & Price</span>
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- TAB 2: SUBSCRIBERS & RESTAURANTS LIST -->
        <div v-if="activeAdminTab === 'subscribers'" class="space-y-6">
          <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
              <h2 class="text-lg font-bold text-white">Registered Clients & Subscriptions</h2>
              <p class="text-xs text-slate-400">Clients ka trial extend karein, 1-click me paid plan mark karein ya plan change karein.</p>
            </div>
            <div class="flex items-center gap-2">
              <input
                v-model="subscriberSearch"
                type="text"
                placeholder="Search restaurant or owner..."
                class="px-3.5 py-2 bg-slate-900 border border-slate-800 rounded-xl text-xs text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30"
              />
            </div>
          </div>

          <!-- Table Container -->
          <div class="bg-slate-900 border border-slate-800 rounded-3xl overflow-hidden shadow-xl">
            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead class="bg-slate-950 text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-800">
                  <tr>
                    <th class="py-3.5 px-4 font-bold">Restaurant & Owner</th>
                    <th class="py-3.5 px-4 font-bold">Current Plan</th>
                    <th class="py-3.5 px-4 font-bold">Status</th>
                    <th class="py-3.5 px-4 font-bold">Time Remaining</th>
                    <th class="py-3.5 px-4 font-bold">Expiry Date</th>
                    <th class="py-3.5 px-4 font-bold text-right">Quick Actions</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                  <tr
                    v-for="rest in filteredSubscribers"
                    :key="rest.id"
                    class="hover:bg-slate-800/40 transition"
                  >
                    <!-- Restaurant & Owner -->
                    <td class="py-3.5 px-4">
                      <div class="font-extrabold text-sm text-white">{{ rest.name }}</div>
                      <div class="text-[11px] text-slate-400 mt-0.5">
                        {{ rest.owner_name || 'Owner' }} • {{ rest.owner_phone || rest.owner_email || 'No contact' }}
                      </div>
                    </td>

                    <!-- Current Plan -->
                    <td class="py-3.5 px-4">
                      <div class="font-bold text-amber-400 capitalize">
                        {{ rest.active_subscription?.plan_name || rest.subscription_plan }}
                      </div>
                      <div class="text-[10px] text-slate-500 capitalize">
                        Cycle: {{ rest.active_subscription?.billing_cycle || 'Trial' }}
                      </div>
                    </td>

                    <!-- Status Badge -->
                    <td class="py-3.5 px-4">
                      <span
                        v-if="rest.subscription_status === 'trial'"
                        class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-cyan-500/20 text-cyan-300 border border-cyan-500/40 inline-flex items-center gap-1"
                      >
                        <i class="fa-solid fa-clock"></i> 30-Day Free Trial
                      </span>
                      <span
                        v-else-if="rest.subscription_status === 'active'"
                        class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 inline-flex items-center gap-1"
                      >
                        <i class="fa-solid fa-circle-check"></i> Active Paid
                      </span>
                      <span
                        v-else
                        class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-rose-500/20 text-rose-300 border border-rose-500/40 inline-flex items-center gap-1"
                      >
                        <i class="fa-solid fa-triangle-exclamation"></i> Expired
                      </span>
                    </td>

                    <!-- Time Remaining -->
                    <td class="py-3.5 px-4">
                      <div
                        class="font-black text-xs"
                        :class="rest.days_remaining > 5 ? 'text-emerald-400' : (rest.days_remaining > 0 ? 'text-amber-400 animate-pulse' : 'text-rose-400')"
                      >
                        {{ rest.days_remaining > 0 ? `${rest.days_remaining} Days Left` : 'Expired' }}
                      </div>
                    </td>

                    <!-- Expiry Date -->
                    <td class="py-3.5 px-4 text-slate-400">
                      {{ formatDate(rest.subscription_ends_at || rest.trial_ends_at) }}
                    </td>

                    <!-- Quick Actions -->
                    <td class="py-3.5 px-4 text-right">
                      <div class="flex items-center justify-end gap-1.5">
                        <button
                          @click="quickExtendTrial(rest, 30)"
                          class="px-2.5 py-1.5 bg-slate-800 hover:bg-slate-700 text-cyan-400 hover:text-cyan-300 rounded-lg text-[11px] font-bold transition cursor-pointer"
                          title="Extend Trial by 30 days"
                        >
                          +30d Trial
                        </button>
                        <button
                          @click="quickMarkPaid(rest)"
                          class="px-2.5 py-1.5 bg-emerald-600/20 hover:bg-emerald-600/30 text-emerald-300 border border-emerald-500/40 rounded-lg text-[11px] font-bold transition cursor-pointer"
                          title="Mark 1 Year Paid"
                        >
                          Mark Paid
                        </button>
                        <button
                          @click="openAssignModal(rest)"
                          class="px-2.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 rounded-lg text-[11px] font-black transition cursor-pointer shadow-xs"
                        >
                          Assign Plan
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Modal: Edit / Create Plan -->
    <div
      v-if="showPlanModal"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
      @click.self="showPlanModal = false"
    >
      <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl text-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
          <div class="flex items-center gap-2">
            <i class="fa-solid fa-sliders text-amber-400"></i>
            <h3 class="font-extrabold text-base text-white">
              {{ editingPlanId ? 'Customize Plan Features & Price' : 'Create New Subscription Plan' }}
            </h3>
          </div>
          <button @click="showPlanModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="savePlan" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-300 mb-1">Plan Name *</label>
            <input
              v-model="planForm.name"
              type="text"
              required
              placeholder="e.g. Pro Growth Plan"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-amber-500/30"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-bold text-slate-300 mb-1">Price Monthly (₹) *</label>
              <input
                v-model.number="planForm.price_monthly"
                type="number"
                min="0"
                required
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
              />
            </div>
            <div>
              <label class="block font-bold text-slate-300 mb-1">Price Yearly (₹) *</label>
              <input
                v-model.number="planForm.price_yearly"
                type="number"
                min="0"
                required
                class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
              />
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1">Badge Text (Optional)</label>
            <input
              v-model="planForm.badge_text"
              type="text"
              placeholder="e.g. Most Popular ⭐ or Best Value"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
            />
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1">Description</label>
            <input
              v-model="planForm.description"
              type="text"
              placeholder="Brief description of who this plan is best for..."
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
            />
          </div>

          <!-- Feature Toggles -->
          <div>
            <div class="flex items-center justify-between mb-2">
              <label class="font-extrabold text-amber-400">Included Features in this Plan:</label>
              <span class="text-[10px] text-slate-400">Toggle ON to include, OFF to drop</span>
            </div>

            <div class="space-y-2 bg-slate-950 p-3 rounded-2xl border border-slate-800 max-h-60 overflow-y-auto">
              <label
                v-for="(def, key) in featureDefinitions"
                :key="key"
                class="flex items-center justify-between p-2 rounded-xl hover:bg-slate-900 transition cursor-pointer"
              >
                <div>
                  <div class="font-bold text-white">{{ def.label }}</div>
                  <div class="text-[10px] text-slate-400">{{ def.description }}</div>
                </div>
                <input
                  type="checkbox"
                  v-model="planForm.features[key]"
                  class="w-4 h-4 accent-amber-500 rounded cursor-pointer"
                />
              </label>
            </div>
          </div>

          <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showPlanModal = false"
              class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingPlan"
              class="px-5 py-2 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-black rounded-xl shadow-lg transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <i v-if="savingPlan" class="fa-solid fa-circle-notch fa-spin"></i>
              <span>Save Plan Changes</span>
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Assign Plan to Restaurant -->
    <div
      v-if="showAssignModal && selectedRestaurant"
      class="fixed inset-0 z-50 bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4"
      @click.self="showAssignModal = false"
    >
      <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl text-slate-100">
        <div class="flex items-center justify-between pb-3 border-b border-slate-800 mb-4">
          <div>
            <h3 class="font-extrabold text-base text-white">Assign Subscription Plan</h3>
            <p class="text-xs text-amber-400">{{ selectedRestaurant.name }}</p>
          </div>
          <button @click="showAssignModal = false" class="text-slate-400 hover:text-white">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <form @submit.prevent="submitAssignPlan" class="space-y-4 text-xs">
          <div>
            <label class="block font-bold text-slate-300 mb-1">Select Plan *</label>
            <select
              v-model="assignForm.plan_id"
              required
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
            >
              <option v-for="p in plans" :key="p.id" :value="p.id">
                {{ p.name }} (₹{{ p.price_monthly }}/mo • ₹{{ p.price_yearly }}/yr)
              </option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1">Billing Cycle *</label>
            <div class="grid grid-cols-2 gap-2">
              <button
                type="button"
                @click="assignForm.cycle = 'monthly'"
                :class="[
                  'py-2 px-3 rounded-xl font-bold border transition text-center cursor-pointer',
                  assignForm.cycle === 'monthly' ? 'bg-amber-500 text-slate-950 border-amber-500 shadow' : 'bg-slate-950 text-slate-400 border-slate-800'
                ]"
              >
                1 Month (30 Days)
              </button>
              <button
                type="button"
                @click="assignForm.cycle = 'yearly'"
                :class="[
                  'py-2 px-3 rounded-xl font-bold border transition text-center cursor-pointer',
                  assignForm.cycle === 'yearly' ? 'bg-amber-500 text-slate-950 border-amber-500 shadow' : 'bg-slate-950 text-slate-400 border-slate-800'
                ]"
              >
                1 Year (365 Days)
              </button>
            </div>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1">Action Type</label>
            <select
              v-model="assignForm.action"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
            >
              <option value="mark_paid">Mark as Paid (UPI / Cash / Bank Transfer)</option>
              <option value="assign">Free Admin Assignment (VIP / Partner)</option>
            </select>
          </div>

          <div>
            <label class="block font-bold text-slate-300 mb-1">Admin Note (Optional)</label>
            <input
              v-model="assignForm.notes"
              type="text"
              placeholder="e.g. Paid via GPay on 11 Nov, invoice #102"
              class="w-full px-3 py-2 bg-slate-950 border border-slate-800 rounded-xl text-white focus:outline-none"
            />
          </div>

          <div class="pt-3 border-t border-slate-800 flex justify-end gap-2">
            <button
              type="button"
              @click="showAssignModal = false"
              class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-xl font-bold cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingAssign"
              class="px-5 py-2 bg-gradient-to-r from-emerald-500 to-teal-500 text-slate-950 font-black rounded-xl shadow-lg transition flex items-center gap-1.5 cursor-pointer disabled:opacity-50"
            >
              <i v-if="savingAssign" class="fa-solid fa-circle-notch fa-spin"></i>
              <span>Activate Plan</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const loading = ref(true);
const activeAdminTab = ref('plans'); // 'plans' or 'subscribers'
const plans = ref([]);
const featureDefinitions = ref({});
const subscribers = ref([]);
const subscriberSearch = ref('');

// Modals
const showPlanModal = ref(false);
const editingPlanId = ref(null);
const savingPlan = ref(false);
const planForm = ref({
  name: '',
  price_monthly: 499,
  price_yearly: 3999,
  badge_text: '',
  description: '',
  features: {},
});

const showAssignModal = ref(false);
const selectedRestaurant = ref(null);
const savingAssign = ref(false);
const assignForm = ref({
  plan_id: null,
  cycle: 'monthly',
  action: 'mark_paid',
  notes: '',
});

const filteredSubscribers = computed(() => {
  if (!subscriberSearch.value.trim()) return subscribers.value;
  const q = subscriberSearch.value.toLowerCase();
  return subscribers.value.filter(s =>
    (s.name && s.name.toLowerCase().includes(q)) ||
    (s.owner_name && s.owner_name.toLowerCase().includes(q)) ||
    (s.owner_phone && s.owner_phone.includes(q)) ||
    (s.owner_email && s.owner_email.toLowerCase().includes(q))
  );
});

const fetchAdminData = async () => {
  loading.value = true;
  try {
    const [plansRes, subsRes] = await Promise.all([
      axios.get('/admin/plans'),
      axios.get('/admin/subscribers'),
    ]);

    if (plansRes.data.success) {
      plans.value = plansRes.data.plans;
      featureDefinitions.value = plansRes.data.feature_definitions;
    }

    if (subsRes.data.success) {
      subscribers.value = subsRes.data.restaurants;
    }
  } catch (err) {
    console.error('Failed to load admin data', err);
  } finally {
    loading.value = false;
  }
};

const openNewPlanModal = () => {
  editingPlanId.value = null;
  const initFeatures = {};
  for (const k in featureDefinitions.value) {
    initFeatures[k] = featureDefinitions.value[k].default || false;
  }
  planForm.value = {
    name: '',
    price_monthly: 499,
    price_yearly: 4999,
    badge_text: '',
    description: '',
    features: initFeatures,
  };
  showPlanModal.value = true;
};

const editPlan = (plan) => {
  editingPlanId.value = plan.id;
  const currentFeatures = { ...plan.features };
  for (const k in featureDefinitions.value) {
    if (currentFeatures[k] === undefined) {
      currentFeatures[k] = false;
    }
  }
  planForm.value = {
    name: plan.name,
    price_monthly: Number(plan.price_monthly),
    price_yearly: Number(plan.price_yearly),
    badge_text: plan.badge_text || '',
    description: plan.description || '',
    features: currentFeatures,
  };
  showPlanModal.value = true;
};

const savePlan = async () => {
  savingPlan.value = true;
  try {
    if (editingPlanId.value) {
      await axios.put(`/admin/plans/${editingPlanId.value}`, planForm.value);
    } else {
      await axios.post('/admin/plans', planForm.value);
    }
    showPlanModal.value = false;
    await fetchAdminData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to save plan');
  } finally {
    savingPlan.value = false;
  }
};

const deletePlan = async (id) => {
  if (!confirm('Are you sure you want to delete this subscription plan?')) return;
  try {
    await axios.delete(`/admin/plans/${id}`);
    await fetchAdminData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to delete plan');
  }
};

const quickExtendTrial = async (rest, days = 30) => {
  try {
    const res = await axios.post(`/admin/subscribers/${rest.id}/assign-plan`, {
      action: 'extend_trial',
      custom_days: days,
    });
    alert(res.data.message);
    await fetchAdminData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to extend trial');
  }
};

const quickMarkPaid = async (rest) => {
  const vipPlan = plans.value.find(p => p.slug === 'vip') || plans.value[0];
  if (!vipPlan) return;
  if (!confirm(`Mark ${rest.name} as Paid for 1 Year on ${vipPlan.name}?`)) return;

  try {
    const res = await axios.post(`/admin/subscribers/${rest.id}/assign-plan`, {
      plan_id: vipPlan.id,
      cycle: 'yearly',
      action: 'mark_paid',
      notes: '1-Year Paid activated via Super Admin',
    });
    alert(res.data.message);
    await fetchAdminData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to activate plan');
  }
};

const openAssignModal = (rest) => {
  selectedRestaurant.value = rest;
  assignForm.value = {
    plan_id: plans.value[0]?.id || null,
    cycle: 'yearly',
    action: 'mark_paid',
    notes: '',
  };
  showAssignModal.value = true;
};

const submitAssignPlan = async () => {
  if (!selectedRestaurant.value) return;
  savingAssign.value = true;
  try {
    const res = await axios.post(`/admin/subscribers/${selectedRestaurant.value.id}/assign-plan`, assignForm.value);
    alert(res.data.message);
    showAssignModal.value = false;
    await fetchAdminData();
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to assign plan');
  } finally {
    savingAssign.value = false;
  }
};

const formatDate = (dateStr) => {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return d.toLocaleDateString([], { day: 'numeric', month: 'short', year: 'numeric' });
};

onMounted(() => {
  fetchAdminData();
});
</script>
