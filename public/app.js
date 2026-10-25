(function () {
  'use strict';
  const TZ = 'Africa/Lagos';
  const $ = (id) => document.getElementById(id);
  const form = $('form');
  const msg = $('msg');

  // Informational clock only; the real timestamp is created by the server.
  function tick() {
    const now = new Date();
    $('clockDate').textContent = now.toLocaleDateString('en-GB', { timeZone: TZ, weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
    $('clockTime').textContent = now.toLocaleTimeString('en-GB', { timeZone: TZ });
  }
  tick();
  setInterval(tick, 1000);

  function todayLagos() {
    return new Date().toLocaleDateString('en-CA', { timeZone: TZ }); // YYYY-MM-DD
  }

  function setMessage(text, ok) {
    msg.textContent = text;
    msg.className = ok ? 'ok' : 'bad';
  }

  function clearErrors() {
    form.querySelectorAll('.err').forEach((e) => (e.textContent = ''));
    form.querySelectorAll('input').forEach((i) => i.classList.remove('invalid'));
  }

  function showErrors(errors) {
    Object.keys(errors || {}).forEach((field) => {
      const el = form.querySelector('[data-for="' + field + '"]');
      const input = form.elements[field];
      if (el) el.textContent = errors[field];
      if (input) input.classList.add('invalid');
    });
  }

  async function submit(type, button) {
    clearErrors();
    setMessage('', true);
    const payload = { type: type };
    ['employee_id', 'full_name', 'department', 'phone'].forEach((n) => (payload[n] = form.elements[n].value.trim()));

    const buttons = form.querySelectorAll('button');
    buttons.forEach((b) => (b.disabled = true));
    try {
      const res = await fetch('api/record.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const json = await res.json();
      if (res.ok && json.success) {
        const t = new Date(json.data.recorded_at.replace(' ', 'T'));
        setMessage(json.message + ' Recorded at ' + json.data.recorded_at.slice(11) + '.', true);
        $('date').value = todayLagos();
        loadRecords();
      } else {
        showErrors(json.errors);
        setMessage(json.message || 'Something went wrong.', false);
      }
    } catch (e) {
      setMessage('Network error. Please try again.', false);
    } finally {
      buttons.forEach((b) => (b.disabled = false));
    }
  }

  let submitter = null;
  form.querySelectorAll('button').forEach((b) => b.addEventListener('click', () => (submitter = b)));
  form.addEventListener('submit', (e) => {
    e.preventDefault();
    if (submitter) submit(submitter.dataset.type, submitter);
  });

  // ---- Records table (built with textContent, so user data can never inject HTML) ----
  function cell(text) {
    const td = document.createElement('td');
    td.textContent = text;
    return td;
  }

  async function loadRecords() {
    const params = new URLSearchParams({ date: $('date').value, search: $('search').value.trim() });
    const body = $('rows');
    try {
      const res = await fetch('api/records.php?' + params.toString());
      const json = await res.json();
      body.replaceChildren();
      const records = (json.data && json.data.records) || [];
      if (!records.length) {
        const tr = document.createElement('tr');
        const td = cell('No records for this date.');
        td.colSpan = 5;
        td.className = 'empty';
        tr.appendChild(td);
        body.appendChild(tr);
        return;
      }
      records.forEach((r) => {
        const tr = document.createElement('tr');
        tr.appendChild(cell(r.recorded_at.slice(11)));
        tr.appendChild(cell(r.employee_id));
        tr.appendChild(cell(r.full_name));
        tr.appendChild(cell(r.department));
        const typeCell = document.createElement('td');
        const tag = document.createElement('span');
        tag.className = 'tag ' + r.type;
        tag.textContent = r.type === 'check_in' ? 'Check in' : 'Check out';
        typeCell.appendChild(tag);
        tr.appendChild(typeCell);
        body.appendChild(tr);
      });
    } catch (e) {
      body.replaceChildren();
    }
  }

  let timer = null;
  $('date').addEventListener('change', loadRecords);
  $('search').addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(loadRecords, 250);
  });

  $('date').value = todayLagos();
  loadRecords();
})();
