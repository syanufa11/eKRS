     <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

     <script>
         document.addEventListener('livewire:init', () => {
             const getEventData = (event) => (Array.isArray(event) ? event[0] : event);

             // 1. Notifikasi (Sukses, Gagal, Info)
             Livewire.on('notify', (event) => {
                 const data = getEventData(event);

                 // Logika penentuan Judul berdasarkan type
                 let title = 'Berhasil!';
                 let btnClass = 'bg-indigo-600';

                 if (data.type === 'error') {
                     title = 'Gagal!';
                     btnClass = 'bg-red-600';
                 } else if (data.type === 'info') {
                     title = 'Informasi';
                     btnClass = 'bg-blue-500';
                 }

                 Swal.fire({
                     title: title,
                     text: data.message,
                     icon: data.type || 'success', // 'success', 'error', 'info', 'warning', 'question'
                     confirmButtonText: 'OK',
                     customClass: {
                         confirmButton: `px-6 py-2 rounded-lg text-white font-semibold ${btnClass}`
                     },
                     buttonsStyling: false
                 });
             });

             // 2. Konfirmasi Buang ke Sampah (Soft Delete)
             Livewire.on('confirm-trash', (event) => {
                 const data = getEventData(event);
                 Swal.fire({
                     title: 'Buang ke Sampah?',
                     text: data.message || 'Data akan dipindahkan ke keranjang sampah.',
                     icon: 'warning',
                     showCancelButton: true,
                     confirmButtonColor: '#f97316',
                     cancelButtonColor: '#6b7280',
                     confirmButtonText: 'Ya, Buang!',
                     cancelButtonText: 'Batal',
                     reverseButtons: true
                 }).then((result) => {
                     if (result.isConfirmed) Livewire.dispatch('trash-confirmed', {
                         id: data.id
                     });
                 });
             });

             // 3. Konfirmasi Pulihkan Data (Restore)
             Livewire.on('confirm-restore', (event) => {
                 const data = getEventData(event);
                 Swal.fire({
                     title: 'Pulihkan Data?',
                     text: data.message || 'Data akan dikembalikan ke daftar aktif.',
                     icon: 'question',
                     showCancelButton: true,
                     confirmButtonColor: '#10b981', // Hijau Emerald
                     cancelButtonColor: '#6b7280',
                     confirmButtonText: 'Ya, Pulihkan!',
                     cancelButtonText: 'Batal',
                     reverseButtons: true
                 }).then((result) => {
                     if (result.isConfirmed) Livewire.dispatch('restore-confirmed', {
                         id: data.id
                     });
                 });
             });

             // 4. Konfirmasi Hapus Permanen (Force Delete)
             Livewire.on('confirm-force-delete', (event) => {
                 const data = getEventData(event);
                 Swal.fire({
                     title: 'Hapus Permanen?',
                     html: `<p>${data.message}</p><p class="text-red-600 text-sm mt-2 font-bold">Tindakan ini tidak dapat dibatalkan!</p>`,
                     icon: 'error',
                     showCancelButton: true,
                     confirmButtonColor: '#ef4444', // Merah
                     cancelButtonColor: '#6b7280',
                     confirmButtonText: 'Ya, Hapus Permanen!',
                     cancelButtonText: 'Batal',
                     reverseButtons: true
                 }).then((result) => {
                     if (result.isConfirmed) Livewire.dispatch('force-delete-confirmed', {
                         id: data.id
                     });
                 });
             });
         });

         @if(session()->has('notify'))
         // Kita panggil event 'notify' secara manual di sisi client
         // Data diambil dari session Laravel
         const flashData = @json(session('notify'));
         Livewire.dispatch('notify', [flashData]);
         @endif
     

     </script>
