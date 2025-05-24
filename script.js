const loginForm = document.getElementById('loginForm');
const forgotForm = document.getElementById('forgotForm');
const forgotPasswordLink = document.getElementById('forgotPasswordLink');
const backToLogin = document.getElementById('backToLogin');
const darkToggle = document.getElementById('darkToggle');

// إظهار وإخفاء النماذج
forgotPasswordLink.addEventListener('click', (e) => {
  e.preventDefault();
  loginForm.style.display = 'none';
  forgotForm.style.display = 'block';
});
backToLogin.addEventListener('click', (e) => {
  e.preventDefault();
  forgotForm.style.display = 'none';
  loginForm.style.display = 'block';
});

// تبديل الوضع الليلي
darkToggle.addEventListener('click', () => {
  document.body.classList.toggle('dark');
  darkToggle.textContent = document.body.classList.contains('dark') ? 'Light Mode' : 'Dark Mode';
});

// التحقق البسيط قبل الإرسال، ثم الإرسال الطبيعي للنموذج
loginForm.addEventListener('submit', (e) => {
  const email = loginForm.querySelector('input[name="email"]').value.trim();
  const password = loginForm.querySelector('input[name="password"]').value;

  if (!email || !password) {
    e.preventDefault(); // نمنع الإرسال فقط إن كانت الحقول فارغة
    alert('Please fill in all fields');
  }
});

// إرسال طلب إعادة تعيين كلمة المرور عبر fetch
forgotForm.querySelector('button').addEventListener('click', async (e) => {
  e.preventDefault();
  const email = forgotForm.querySelector('input[type="email"]').value.trim();

  if (!email) {
    alert('Please enter your email');
    return;
  }

  try {
    const res = await fetch('reset_password.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `email=${encodeURIComponent(email)}`
    });

    if (!res.ok) throw new Error('Network response was not OK');

    const data = await res.json();
    alert(data.message);
  } catch (err) {
    alert("Failed to connect to the server");
    console.error(err);
  }
});
