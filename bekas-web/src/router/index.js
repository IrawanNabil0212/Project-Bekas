import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import RegisterView from '../views/RegisterView.vue'
import HomeView from '../views/HomeView.vue'
import JualView from '../views/JualView.vue'
import ProfileView from '../views/ProfileView.vue'
import ProductDetail from '../views/ProductDetail.vue'
import CartView from '../views/CartView.vue'
import PaymentView from '../views/PaymentView.vue'
// IMPORT HALAMAN BARU
import OrdersView from '../views/OrdersView.vue'

const routes = [
  { path: '/', name: 'home', component: HomeView, meta: { requiresAuth: true } },
  { path: '/login', name: 'login', component: LoginView, meta: { guestOnly: true } },
  { path: '/register', name: 'register', component: RegisterView, meta: { guestOnly: true } },
  { path: '/jual', name: 'jual', component: JualView, meta: { requiresAuth: true } },
  { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
  { path: '/product/:id', name: 'product-detail', component: ProductDetail, meta: { requiresAuth: true } },
  { path: '/cart', name: 'cart', component: CartView, meta: { requiresAuth: true } },
  { path: '/payment', name: 'payment', component: PaymentView, meta: { requiresAuth: true } },
  // TAMBAHKAN ROUTE PESANAN
  { path: '/orders', name: 'orders', component: OrdersView, meta: { requiresAuth: true } }
]

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return { top: 0 }
  }
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  if (to.meta.requiresAuth && !token) {
    next('/login');
  } else if (to.meta.guestOnly && token) {
    next('/');
  } else {
    next();
  }
});

export default router