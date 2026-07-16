/**
 * Клиентский сценарий для страницы бронирования столов.
 * Управляет интерактивным календарем выбора даты, динамической загрузкой слотов времени, валидацией формы и отправкой заявки.
 */

document.addEventListener('DOMContentLoaded', () => {

    const form = document.getElementById('table-booking-form');

    if (window.currentUser) {
        const nameInput = document.getElementById('booking-name');
        const phoneInput2 = document.getElementById('booking-phone');
        const emailInput = document.getElementById('booking-email');
        if (nameInput && !nameInput.value) nameInput.value = window.currentUser.name || '';
        if (phoneInput2 && !phoneInput2.value) phoneInput2.value = window.currentUser.phone || '';
        if (emailInput && !emailInput.value) emailInput.value = window.currentUser.email || '';
    }

    const dateInput = document.getElementById('booking-date');
    const calendarPopup = document.getElementById('custom-calendar-popup');
    const dateWrapper = document.querySelector('.custom-date-input-wrapper');
    
    let currentYear = new Date().getFullYear();
    let currentMonth = new Date().getMonth();

    const monthNames = [
        "Январь", "Февраль", "Март", "Апрель", "Май", "Июнь",
        "Июль", "Август", "Сентябрь", "Октябрь", "Ноябрь", "Декабрь"
    ];

    /* Рендеринг сетки кастомного календаря с блокировкой прошлых дат */
    function renderCustomCalendar(year, month) {
        if (!calendarPopup || !dateInput) return;
        calendarPopup.innerHTML = '';

        const todayObj = new Date();
        todayObj.setHours(0,0,0,0);

        const firstDayIndex = (new Date(year, month, 1).getDay() + 6) % 7;
        const totalDays = new Date(year, month + 1, 0).getDate();
        const prevMonthTotalDays = new Date(year, month, 0).getDate();

        const header = document.createElement('div');
        header.className = 'calendar-header';

        const title = document.createElement('h4');
        title.textContent = `${monthNames[month]} ${year}`;

        const prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'calendar-nav-btn';
        prevBtn.innerHTML = '←';
        prevBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            currentMonth--;
            if (currentMonth < 0) {
                currentMonth = 11;
                currentYear--;
            }
            renderCustomCalendar(currentYear, currentMonth);
        });

        const nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'calendar-nav-btn';
        nextBtn.innerHTML = '→';
        nextBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            currentMonth++;
            if (currentMonth > 11) {
                currentMonth = 0;
                currentYear++;
            }
            renderCustomCalendar(currentYear, currentMonth);
        });

        const lastDayOfPrevMonthObj = new Date(year, month, 0);
        if (lastDayOfPrevMonthObj < todayObj) {
            prevBtn.disabled = true;
            prevBtn.style.opacity = 0.3;
            prevBtn.style.cursor = 'not-allowed';
        }

        header.appendChild(prevBtn);
        header.appendChild(title);
        header.appendChild(nextBtn);
        calendarPopup.appendChild(header);

        const grid = document.createElement('div');
        grid.className = 'calendar-grid';

        const weekdays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        weekdays.forEach(wd => {
            const el = document.createElement('div');
            el.className = 'calendar-weekday';
            el.textContent = wd;
            grid.appendChild(el);
        });

        for (let i = firstDayIndex; i > 0; i--) {
            const dayNum = prevMonthTotalDays - i + 1;
            const el = document.createElement('div');
            el.className = 'calendar-day disabled other-month';
            el.textContent = dayNum;
            grid.appendChild(el);
        }

        let selectedDateStr = dateInput.value;
        let selectedDay = null;
        let selectedMonth = null;
        let selectedYear = null;
        if (selectedDateStr) {
            const parts = selectedDateStr.split('-');
            if (parts.length === 3) {
                selectedYear = parseInt(parts[0]);
                selectedMonth = parseInt(parts[1]) - 1;
                selectedDay = parseInt(parts[2]);
            }
        }

        for (let day = 1; day <= totalDays; day++) {
            const el = document.createElement('div');
            el.className = 'calendar-day';
            el.textContent = day;

            const dateOfSlot = new Date(year, month, day);
            dateOfSlot.setHours(0,0,0,0);

            if (dateOfSlot < todayObj) {
                el.classList.add('disabled');
            } else {
                const isToday = (
                    dateOfSlot.getDate() === todayObj.getDate() &&
                    dateOfSlot.getMonth() === todayObj.getMonth() &&
                    dateOfSlot.getFullYear() === todayObj.getFullYear()
                );
                if (isToday) el.classList.add('today');

                const isSelected = (
                    day === selectedDay &&
                    month === selectedMonth &&
                    year === selectedYear
                );
                if (isSelected) el.classList.add('selected');

                el.addEventListener('click', (e) => {
                    e.stopPropagation();
                    let mm = month + 1;
                    let dd = day;
                    if (mm < 10) mm = '0' + mm;
                    if (dd < 10) dd = '0' + dd;
                    const formattedDate = `${year}-${mm}-${dd}`;
                    
                    dateInput.value = formattedDate;
                    dateInput.dispatchEvent(new Event('change'));
                    
                    calendarPopup.classList.remove('active');
                });
            }
            grid.appendChild(el);
        }

        const totalCellsSoFar = firstDayIndex + totalDays;
        const remainder = totalCellsSoFar % 7;
        if (remainder > 0) {
            const nextDaysNeeded = 7 - remainder;
            for (let day = 1; day <= nextDaysNeeded; day++) {
                const el = document.createElement('div');
                el.className = 'calendar-day disabled other-month';
                el.textContent = day;
                grid.appendChild(el);
            }
        }

        calendarPopup.appendChild(grid);
    }

    if (dateWrapper && calendarPopup) {
        dateWrapper.addEventListener('click', (e) => {
            e.stopPropagation();
            calendarPopup.classList.toggle('active');
            if (calendarPopup.classList.contains('active')) {
                let selectedDateStr = dateInput.value;
                if (selectedDateStr) {
                    const parts = selectedDateStr.split('-');
                    if (parts.length === 3) {
                        currentYear = parseInt(parts[0]);
                        currentMonth = parseInt(parts[1]) - 1;
                    }
                } else {
                    currentYear = new Date().getFullYear();
                    currentMonth = new Date().getMonth();
                }
                renderCustomCalendar(currentYear, currentMonth);
            }
        });

        document.addEventListener('click', (e) => {
            if (!calendarPopup.contains(e.target) && !dateWrapper.contains(e.target)) {
                calendarPopup.classList.remove('active');
            }
        });
    }

    const timeSelect   = document.getElementById('booking-time');
    const slotsLoading = document.getElementById('slots-loading');
    const chipsContainer = document.getElementById('booking-time-slots');

    /* Загрузка доступных слотов времени с сервера для выбранной даты */
    async function loadSlots(date) {
        if (!timeSelect) return;

        timeSelect.disabled = true;
        if (slotsLoading) slotsLoading.style.display = 'inline';
        if (chipsContainer) {
            chipsContainer.innerHTML = '<div class="time-slots-placeholder">⏳ Загружаем доступные слоты...</div>';
        }

        timeSelect.innerHTML = '<option value="">⏳ Загрузка...</option>';

        try {
            const res = await fetch(
                `php/api/booking.php?action=get_available_slots&date=${encodeURIComponent(date)}`,
                { method: 'GET', headers: { 'Content-Type': 'application/json' } }
            );
            const data = await res.json();

            if (!data.success || !Array.isArray(data.slots)) {
                throw new Error(data.message || 'Ошибка загрузки слотов');
            }

            const availableCount = data.slots.filter(s => s.available).length;

            timeSelect.innerHTML = '';
            if (chipsContainer) chipsContainer.innerHTML = '';

            if (availableCount === 0) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '— все слоты на эту дату заняты —';
                opt.disabled = true;
                opt.selected = true;
                timeSelect.appendChild(opt);

                if (chipsContainer) {
                    chipsContainer.innerHTML = '<div class="time-slots-placeholder">— все слоты на эту дату заняты —</div>';
                }
            } else {
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '— выберите время —';
                placeholder.disabled = true;
                placeholder.selected = true;
                timeSelect.appendChild(placeholder);

                data.slots.forEach(slot => {
                    const opt = document.createElement('option');
                    opt.value = slot.time + ':00';
                    opt.textContent = slot.time;
                    if (!slot.available) opt.disabled = true;
                    timeSelect.appendChild(opt);

                    if (chipsContainer) {
                        const chip = document.createElement('div');
                        chip.className = 'time-slot-chip';
                        chip.textContent = slot.time;

                        if (!slot.available) {
                            chip.classList.add('disabled');
                        } else {
                            chip.addEventListener('click', () => {
                                chipsContainer.querySelectorAll('.time-slot-chip').forEach(c => c.classList.remove('selected'));
                                chip.classList.add('selected');
                                timeSelect.value = slot.time + ':00';
                                timeSelect.dispatchEvent(new Event('change'));
                            });
                        }
                        chipsContainer.appendChild(chip);
                    }
                });

                timeSelect.disabled = false;
            }

        } catch (err) {
            timeSelect.innerHTML = '<option value="">— ошибка загрузки слотов —</option>';
            if (chipsContainer) {
                chipsContainer.innerHTML = '<div class="time-slots-placeholder" style="color: #ff4d4d;">— ошибка загрузки слотов —</div>';
            }
            console.error('Ошибка загрузки слотов:', err.message);
        } finally {
            if (slotsLoading) slotsLoading.style.display = 'none';
        }
    }

    if (dateInput) {
        dateInput.addEventListener('change', () => {
            const selectedDate = dateInput.value;
            if (selectedDate) {
                loadSlots(selectedDate);
            } else {
                if (timeSelect) {
                    timeSelect.innerHTML = '<option value="">— сначала выберите дату —</option>';
                    timeSelect.disabled = true;
                }
                if (chipsContainer) {
                    chipsContainer.innerHTML = '<div class="time-slots-placeholder">— сначала выберите дату —</div>';
                }
            }
        });
    }

    const phoneInput = document.getElementById('booking-phone');
    if (phoneInput) {
        phoneInput.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');

            if (value.startsWith('7') || value.startsWith('8')) {
                value = value.substring(1);
            }

            let formatted = '+7 ';
            if (value.length > 0) {
                formatted += '(' + value.substring(0, 3);
            }
            if (value.length >= 4) {
                formatted += ') ' + value.substring(3, 6);
            }
            if (value.length >= 7) {
                formatted += '-' + value.substring(6, 8);
            }
            if (value.length >= 9) {
                formatted += '-' + value.substring(8, 10);
            }

            e.target.value = value.length === 0 ? '' : formatted;
        });

        phoneInput.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && phoneInput.value.length <= 4) {
                phoneInput.value = '';
            }
        });
    }

    const modal = document.getElementById('booking-success-modal');
    const modalBtn = document.getElementById('booking-modal-btn');
    const modalOverlay = document.querySelector('.booking-modal__overlay');

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();

            let isValid = true;

            const nameEl = document.getElementById('booking-name');
            const nameGroup = nameEl.closest('.form-group');
            const nameErr = nameGroup.querySelector('.error-message');
            if (nameEl.value.trim().length < 2) {
                nameGroup.classList.add('invalid');
                nameErr.textContent = 'Имя должно содержать не менее 2 символов';
                isValid = false;
            } else {
                nameGroup.classList.remove('invalid');
                nameErr.textContent = '';
            }

            const phoneEl = document.getElementById('booking-phone');
            const phoneGroup = phoneEl.closest('.form-group');
            const phoneErr = phoneGroup.querySelector('.error-message');
            const phoneRegex = /^\+7 \(\d{3}\) \d{3}-\d{2}-\d{2}$/;
            if (!phoneRegex.test(phoneEl.value)) {
                phoneGroup.classList.add('invalid');
                phoneErr.textContent = 'Введите корректный номер телефона';
                isValid = false;
            } else {
                phoneGroup.classList.remove('invalid');
                phoneErr.textContent = '';
            }

            const emailEl = document.getElementById('booking-email');
            const emailGroup = emailEl.closest('.form-group');
            const emailErr = emailGroup.querySelector('.error-message');
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(emailEl.value.trim())) {
                emailGroup.classList.add('invalid');
                emailErr.textContent = 'Введите корректный адрес электронной почты';
                isValid = false;
            } else {
                emailGroup.classList.remove('invalid');
                emailErr.textContent = '';
            }

            const dateEl = document.getElementById('booking-date');
            const dateGroup = dateEl.closest('.form-group');
            const dateErr = dateGroup.querySelector('.error-message');
            if (!dateEl.value) {
                dateGroup.classList.add('invalid');
                dateErr.textContent = 'Выберите дату бронирования';
                isValid = false;
            } else {
                dateGroup.classList.remove('invalid');
                dateErr.textContent = '';
            }

            const timeEl = document.getElementById('booking-time');
            const timeGroup = document.getElementById('booking-time-group');
            const timeErr = timeGroup.querySelector('.error-message');
            if (!timeEl.value || timeEl.value === '') {
                timeGroup.classList.add('invalid');
                timeErr.textContent = dateEl.value
                    ? 'Выберите время бронирования'
                    : 'Сначала выберите дату, затем время';
                isValid = false;
            } else {
                timeGroup.classList.remove('invalid');
                timeErr.textContent = '';
            }

            const guestsEl = document.getElementById('booking-guests');
            const guestsGroup = guestsEl.closest('.form-group');
            const guestsErr = guestsGroup.querySelector('.error-message');
            const guestsCount = parseInt(guestsEl.value);
            if (isNaN(guestsCount) || guestsCount < 1 || guestsCount > 20) {
                guestsGroup.classList.add('invalid');
                guestsErr.textContent = 'Укажите количество гостей от 1 до 20';
                isValid = false;
            } else {
                guestsGroup.classList.remove('invalid');
                guestsErr.textContent = '';
            }

            if (isValid) {
                const submitBtn = form.querySelector('.booking-submit-btn');
                if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Отправляем...'; }

                const nameEl2    = document.getElementById('booking-name');
                const phoneEl2   = document.getElementById('booking-phone');
                const emailEl2   = document.getElementById('booking-email');
                const guestsEl2  = document.getElementById('booking-guests');
                const dateEl2    = document.getElementById('booking-date');
                const timeEl2    = document.getElementById('booking-time');
                const commentEl  = document.getElementById('booking-comment');

                fetch('php/api/booking.php?action=create', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        name:         nameEl2.value.trim(),
                        phone:        phoneEl2.value,
                        email:        emailEl2.value.trim(),
                        guests_count: parseInt(guestsEl2.value),
                        booking_date: dateEl2.value,
                        booking_time: timeEl2.value,
                        comment:      commentEl ? commentEl.value.trim() : ''
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        if (modal) modal.classList.add('active');
                        form.reset();
                        if (timeSelect) {
                            timeSelect.innerHTML = '<option value="">— сначала выберите дату —</option>';
                            timeSelect.disabled = true;
                        }
                        if (chipsContainer) {
                            chipsContainer.innerHTML = '<div class="time-slots-placeholder">— сначала выберите дату —</div>';
                        }
                    } else {
                        alert('Ошибка: ' + data.message);
                        if (dateEl2.value) loadSlots(dateEl2.value);
                    }
                })
                .catch(() => { alert('Ошибка сети. Попробуйте позже.'); })
                .finally(() => {
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Подтвердить бронирование'; }
                });

            } else {
                const invalidGroups = form.querySelectorAll('.form-group.invalid');
                invalidGroups.forEach(group => {
                    group.classList.remove('shake-animation');
                    void group.offsetWidth;
                    group.classList.add('shake-animation');
                });
            }
        });
    }

    const closeModal = () => {
        if (modal) modal.classList.remove('active');
    };

    if (modalBtn) modalBtn.addEventListener('click', closeModal);
    if (modalOverlay) modalOverlay.addEventListener('click', closeModal);
});