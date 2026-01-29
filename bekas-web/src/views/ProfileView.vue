<template>
  <div class="page-container">
    
    <header class="desktop-nav">
      <div class="nav-content">
        <span class="brand">🛍️ BekasBerkah</span>
        <div class="menu">
           <a @click="router.push('/')">Beranda</a>
           <a @click="router.push('/jual')">Jual</a>
           <a class="active">Akun Saya</a>
        </div>
      </div>
    </header>

    <div class="mobile-header-bg">
      <div class="m-header-content">
         <h2>Profil Saya</h2>
         <div class="avatar-big">
            {{ user?.name ? user.name.charAt(0).toUpperCase() : '?' }}
         </div>
         <h3>{{ user?.name || 'Loading...' }}</h3>
         <p>{{ user?.email }}</p>
      </div>
    </div>

    <div class="content-wrapper">
      <div class="menu-card" @click="router.push('/orders')">
        <div class="menu-info">
          <span class="menu-icon">📦</span>
          <div>
            <div class="menu-title">Pesanan Saya</div>
            <div class="menu-sub">Cek status pengiriman barangmu</div>
          </div>
        </div>
        <span class="arrow">❯</span>
      </div>

      <div class="profile-card">
        <div class="desktop-card-header">
           <div class="d-avatar">{{ user?.name ? user.name.charAt(0).toUpperCase() : '?' }}</div>
           <div>
             <h2 class="d-name">{{ user?.name }}</h2>
             <p class="d-email">{{ user?.email }}</p>
           </div>
        </div>

        <h3 class="section-label">Informasi Pribadi</h3>
        
        <div class="info-list">
           <div class="info-row">
             <div class="icon">👤</div>
             <div class="data">
               <label>Nama Lengkap</label>
               <div>{{ user?.name || '-' }}</div>
             </div>
           </div>
           <div class="info-row">
             <div class="icon">📧</div>
             <div class="data">
               <label>Email</label>
               <div>{{ user?.email || '-' }}</div>
             </div>
           </div>
           <div class="info-row">
             <div class="icon">📞</div>
             <div class="data">
               <label>Nomor Telepon</label>
               <div>{{ user?.phone || '-' }}</div>
             </div>
           </div>
           <div class="info-row">
             <div class="icon">📍</div>
             <div class="data">
               <label>Alamat Pengiriman</label>
               <div>{{ user?.address || '-' }}</div>
             </div>
           </div>
        </div>

        <div class="action-buttons">
          <button class="btn-edit" @click="openEditModal">✏️ Edit Profil</button>
          <button class="btn-logout" @click="handleLogout">🚪 Logout</button>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="modal-overlay">
      <div class="modal-content">
        <h3>Edit Profil</h3>
        <form @submit.prevent="updateProfile">
          <div class="form-group">
            <label>Nama</label>
            <input type="text" v-model="editForm.name" required />
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" v-model="editForm.email" required />
          </div>
          <div class="form-group">
            <label>No. HP</label>
            <input type="text" v-model="editForm.phone" placeholder="Contoh: 08123456789" />
          </div>
          <div class="form-group">
            <label>Alamat Lengkap</label>
            <textarea v-model="editForm.address" rows="3"></textarea>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn-cancel" @click="showModal = false">Batal</button>
            <button type="submit" class="btn-save" :disabled="loading">
              {{ loading ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api';

const router = useRouter();
const user = ref(null);
const showModal = ref(false);
const loading = ref(false);
const editForm = ref({ name: '', email: '', phone: '', address: '' });

const fetchUser = async () => {
  try {
    const res = await api.get('/me');
    user.value = res.data;
  } catch (error) {
    localStorage.removeItem('token');
    router.push('/login');
  }
};

const openEditModal = () => {
  if(!user.value) return;
  editForm.value = { 
    name: user.value.name, 
    email: user.value.email, 
    phone: user.value.phone || '', 
    address: user.value.address || '' 
  };
  showModal.value = true;
};

const updateProfile = async () => {
  loading.value = true;
  try {
    const res = await api.put('/profile', editForm.value);
    user.value = res.data.data;
    alert("Profil berhasil diperbarui!");
    showModal.value = false;
    await fetchUser(); 
  } catch (err) {
    alert("Gagal update profil");
  } finally {
    loading.value = false;
  }
};

const handleLogout = async () => {
    try { await api.post('/logout'); } catch(e) {}
    localStorage.removeItem('token');
    router.push('/login');
};

onMounted(fetchUser);
</script>

<style scoped>
.page-container { background: #f4f6f8; min-height: 100vh; padding-bottom: 80px; }
.desktop-nav { background: white; padding: 15px 0; border-bottom: 1px solid #ddd; display: none; }
.nav-content { max-width: 900px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center; padding: 0 20px; }
.brand { font-weight: bold; color: #78866B; font-size: 1.2rem; }
.menu a { margin-left: 20px; cursor: pointer; color: #666; font-size: 0.9rem; font-weight: 500; }
.content-wrapper { max-width: 800px; margin: 0 auto; padding: 20px; position: relative; z-index: 10; }

/* STYLING MENU BARU */
.menu-card { background: white; border-radius: 12px; padding: 15px 20px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
.menu-card:hover { transform: translateY(-2px); }
.menu-info { display: flex; align-items: center; gap: 15px; }
.menu-icon { font-size: 24px; }
.menu-title { font-weight: bold; color: #333; }
.menu-sub { font-size: 0.8rem; color: #888; }
.arrow { color: #ccc; }

.profile-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
.desktop-card-header { display: none; align-items: center; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
.d-avatar { width: 70px; height: 70px; background: #78866B; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 30px; font-weight: bold; }
.section-label { color: #888; font-size: 0.9rem; margin-bottom: 20px; text-transform: uppercase; }
.info-row { display: flex; gap: 20px; margin-bottom: 25px; }
.info-row .icon { font-size: 24px; width: 40px; text-align: center; color: #78866B; }
.info-row label { display: block; font-size: 0.8rem; color: #aaa; font-weight: bold; }
.action-buttons { display: flex; gap: 15px; }
.btn-edit { background: #f0fdf4; color: #78866B; border: 1px solid #78866B; flex: 1; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; }
.btn-logout { background: #fee2e2; color: #dc2626; border: none; flex: 1; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; }

.mobile-header-bg { background: #78866B; padding: 30px 20px 80px; text-align: center; color: white; border-bottom-left-radius: 30px; border-bottom-right-radius: 30px; display: none; }
.avatar-big { width: 80px; height: 80px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.5); border-radius: 50%; margin: 10px auto; display: flex; align-items: center; justify-content: center; font-size: 35px; font-weight: bold; }

@media (min-width: 769px) { .desktop-nav { display: block; } .desktop-card-header { display: flex; } }
@media (max-width: 768px) { .mobile-header-bg { display: block; } .content-wrapper { margin-top: -60px; } .action-buttons { flex-direction: column; } }

.modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; display: flex; align-items: center; justify-content: center; padding: 20px; }
.modal-content { background: white; width: 100%; max-width: 400px; padding: 25px; border-radius: 12px; }
.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-size: 0.8rem; margin-bottom: 5px; color: #666; }
.form-group input, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; }
.btn-save { background: #78866B; color: white; flex: 1; padding: 10px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
.btn-cancel { background: #eee; flex: 1; padding: 10px; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; }
</style>