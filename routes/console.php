<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../api'; // Pastikan path ini benar mengarah ke file api axios

const router = useRouter();
const email = ref('');
const password = ref('');
const isLoading = ref(false);

const handleLogin = async () => {
  // 1. Cek apakah fungsi terpanggil
  console.log("Tombol Masuk Ditekan!");
  console.log("Email:", email.value);
  console.log("Password:", password.value);

  if(!email.value || !password.value) {
      alert("Email dan Password tidak boleh kosong!");
      return;
  }

  isLoading.value = true;

  try {
    console.log("Mencoba mengirim request ke backend...");
    
    // Kirim request
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    });

    // 2. Cek respon dari server
    console.log("Respon dari server:", response);

    if (response.data.success) {
      console.log("Login Sukses!");
      localStorage.setItem('token', response.data.access_token);
      localStorage.setItem('user', JSON.stringify(response.data.data));
      router.push('/');
    } else {
      console.log("Login Gagal (Logic):", response.data.message);
      alert(response.data.message);
    }
  } catch (error) {
    // 3. Cek jika ada error koneksi
    console.error("TERJADI ERROR:", error);
    
    if (error.response) {
        // Server merespon tapi error (misal 401 Unauthorized)
        console.log("Data Error:", error.response.data);
        alert('Gagal: ' + (error.response.data.message || 'Server Error'));
    } else if (error.request) {
        // Server tidak merespon sama sekali (Mati / Network Error)
        alert('Tidak ada respon dari server. Pastikan "php artisan serve" menyala!');
    } else {
        alert('Terjadi kesalahan sistem.');
    }
  } finally {
    isLoading.value = false;
  }
};
</script>