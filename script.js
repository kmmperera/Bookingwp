document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('booking-form');
    const bookingDateInput = document.getElementById('booking-date');
    const timeSlotsContainer = document.getElementById('time-slots-container');
    const bookingResult = document.getElementById('booking-result');

    bookingDateInput.addEventListener('change', function() {
        const selectedDate = bookingDateInput.value;
        timeSlotsContainer.innerHTML = '';

        if (selectedDate) {
            const timeSlots = generateTimeSlots();
            timeSlots.forEach(function(slot) {
                const timeSlotDiv = document.createElement('div');
                timeSlotDiv.classList.add('time-slot');
                timeSlotDiv.innerHTML = `
                    <input type="radio" name="time" value="${slot}" required>
                    <label>${slot}</label>
                `;
                timeSlotsContainer.appendChild(timeSlotDiv);
            });
        } else {
            timeSlotsContainer.innerHTML = '<p>No available time slots for the selected date.</p>';
        }
    });

    bookingForm.addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(bookingForm);
        const bookingData = {
            date: formData.get('booking-date'),
            time: formData.get('time'),
            name: formData.get('name'),
            email: formData.get('email'),
            nonce: sbp_ajax.nonce
        };

        jQuery.post(sbp_ajax.ajax_url, {
            action: 'sbp_handle_booking',
            ...bookingData
        }, function(response) {
            if (response.success) {
                bookingResult.innerHTML = `<p>${response.data}</p>`;
                bookingForm.reset();
                timeSlotsContainer.innerHTML = '';
            } else {
                bookingResult.innerHTML = '<p>Booking failed. Please try again.</p>';
            }
        });
    });

    function generateTimeSlots() {
        const slots = [];
        const startTime = new Date();
        startTime.setHours(9, 0, 0); // Start time at 9:00 AM

        const endTime = new Date();
        endTime.setHours(17, 0, 0); // End time at 5:00 PM

        while (startTime < endTime) {
            slots.push(formatTime(startTime));
            startTime.setMinutes(startTime.getMinutes() + 30);
        }

        return slots;
    }

    function formatTime(date) {
        let hours = date.getHours();
        const minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12; // the hour '0' should be '12'
        const minutesStr = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutesStr + ' ' + ampm;
    }
});
