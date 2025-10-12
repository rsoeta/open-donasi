// Pastikan SweetAlert2 sudah dimuat: <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

function shareDonasi(judul, url) {
  // gunakan \r\n untuk newline agar tampil satu baris kosong antar line di clipboard
  const teksSalin = `${judul}\r\n\r\n${url}`;

  if (navigator.share) {
    // === Web Share API (HP & browser modern)
    navigator.share({
      title: judul,
      text: `Ayo bantu donasi untuk program "${judul}" di Open Donasi!`,
      url: url
    }).catch(err => console.log('Share dibatalkan:', err));
  } else {
    // === Fallback: copy ke clipboard
    try {
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(teksSalin)
          .then(() => showCopiedAlert(judul, url))
          .catch(() => fallbackCopy(teksSalin, judul, url));
      } else {
        fallbackCopy(teksSalin, judul, url);
      }
    } catch (e) {
      fallbackCopy(teksSalin, judul, url);
    }
  }
}

// === Fallback copy universal (untuk semua browser)
function fallbackCopy(teks, judul, url) {
  const tempInput = document.createElement('textarea');
  tempInput.value = teks;
  document.body.appendChild(tempInput);
  tempInput.select();
  document.execCommand('copy');
  document.body.removeChild(tempInput);
  showCopiedAlert(judul, url);
}

// === SweetAlert2 popup
function showCopiedAlert(judul, url) {
  Swal.fire({
    icon: 'success',
    title: 'Link Disalin!',
    html: `
      <div style="text-align:left">
        <strong>${judul}</strong><br><br>
        <a href="${url}" target="_blank">${url}</a>
      </div>
    `,
    confirmButtonColor: '#2c7a7b',
    confirmButtonText: 'OK'
  });
}
