/* ==========================================================================
   STUDENT TECHNOLOGY AND INNOVATION CONFERENCE (STICON 2026)
   JavaScript Engine: js/script.js
   Registration Number: CT100/G/24121/24
   Individual Requirement: Accommodation Option at KSh 1,500 / night
   ========================================================================== */

/**
 * Wait for DOM content to load before attaching event listeners and initializing
 */
document.addEventListener('DOMContentLoaded', function () {
  
  // --- DOM Element References ---
  const registrationForm = document.getElementById('registrationForm');
  const fullNameInput = document.getElementById('full_name');
  const admissionNumberInput = document.getElementById('admission_number');
  const emailInput = document.getElementById('email');
  const phoneInput = document.getElementById('phone');
  const yearSelect = document.getElementById('year');
  const daysInput = document.getElementById('days');
  const workshopSection = document.getElementById('workshopSection');
  const accommodationNightsGroup = document.getElementById('accommodationNightsGroup');
  const accommodationNightsInput = document.getElementById('accommodation_nights');
  const errorMessageContainer = document.getElementById('errorMessage');
  const errorList = document.getElementById('errorList');
  const resetBtn = document.getElementById('resetBtn');

  // Summary DOM Elements
  const dispDays = document.getElementById('dispDays');
  const dispMealDays = document.getElementById('dispMealDays');
  const dispNights = document.getElementById('dispNights');
  const summaryAttendance = document.getElementById('summaryAttendance');
  const summaryWorkshop = document.getElementById('summaryWorkshop');
  const summaryMeals = document.getElementById('summaryMeals');
  const summaryAccommodation = document.getElementById('summaryAccommodation');
  const summaryTotal = document.getElementById('summaryTotal');

  /**
   * Helper function to format numbers with comma separator (e.g., 6350 -> "6,350")
   * @param {number} amount 
   * @returns {string} Formatted string
   */
  function formatMoney(amount) {
    return amount.toLocaleString('en-KE');
  }

  /**
   * FUNCTION 1: showWorkshop()
   * Dynamically shows or hides the workshop options section based on selected participation category.
   * Demonstrates Feature 1 requirement.
   */
  function showWorkshop() {
    const selectedCategory = document.querySelector('input[name="category"]:checked');
    
    if (selectedCategory && selectedCategory.value === 'Workshop Participant') {
      workshopSection.classList.remove('hidden');
      const workshopRadios = workshopSection.querySelectorAll('input[name="workshop"]');
      workshopRadios.forEach(radio => {
        radio.disabled = false;
      });
      // Ensure at least one workshop option is checked if none selected
      const checkedWs = workshopSection.querySelector('input[name="workshop"]:checked');
      if (!checkedWs && workshopRadios.length > 0) {
        workshopRadios[0].checked = true;
      }
    } else {
      workshopSection.classList.add('hidden');
      const workshopRadios = workshopSection.querySelectorAll('input[name="workshop"]');
      workshopRadios.forEach(radio => {
        radio.disabled = true;
      });
    }

    // Recalculate fee immediately
    calculateFee();
  }

  /**
   * FUNCTION 2: showAccommodation()
   * Dynamically shows or hides the accommodation nights input box based on accommodation selection.
   * Individual requirement for Reg No CT100/G/24121/24 (KSh 1,500 / night).
   * Demonstrates Feature 2 requirement.
   */
  function showAccommodation() {
    const selectedAcc = document.querySelector('input[name="accommodation"]:checked');

    if (selectedAcc && selectedAcc.value === 'Require Accommodation') {
      accommodationNightsGroup.classList.remove('hidden');
      accommodationNightsInput.disabled = false;
      if (!accommodationNightsInput.value || parseInt(accommodationNightsInput.value, 10) < 1) {
        accommodationNightsInput.value = 1;
      }
    } else {
      accommodationNightsGroup.classList.add('hidden');
      accommodationNightsInput.disabled = true;
    }

    // Recalculate fee immediately
    calculateFee();
  }

  /**
   * FUNCTION 3: calculateFee()
   * Automatically calculates total fee and itemized breakdown dynamically.
   * Rates:
   *  - Registration: KSh 1,000 (fixed)
   *  - Attendance: KSh 500 per day
   *  - Workshop: KSh 750 (if category === 'Workshop Participant')
   *  - Lunch/Meals: KSh 300 per day (if meals === 'Lunch Required')
   *  - Accommodation: KSh 1,500 per night (if accommodation === 'Require Accommodation')
   */
  function calculateFee() {
    const REGISTRATION_FEE = 1000;
    const ATTENDANCE_RATE = 500;
    const WORKSHOP_RATE = 750;
    const MEAL_RATE = 300;
    const ACCOMMODATION_RATE = 1500;

    // 1. Conference Days
    let days = parseInt(daysInput.value, 10);
    if (isNaN(days) || days < 1) days = 0;

    const attendanceFee = days * ATTENDANCE_RATE;

    // 2. Workshop Fee
    const selectedCategory = document.querySelector('input[name="category"]:checked');
    let workshopFee = 0;
    if (selectedCategory && selectedCategory.value === 'Workshop Participant') {
      const selectedWorkshop = document.querySelector('input[name="workshop"]:checked');
      if (selectedWorkshop) {
        workshopFee = WORKSHOP_RATE;
      }
    }

    // 3. Meal Fee
    const selectedMeal = document.querySelector('input[name="meals"]:checked');
    let mealFee = 0;
    if (selectedMeal && selectedMeal.value === 'Lunch Required') {
      mealFee = days * MEAL_RATE;
    }

    // 4. Accommodation Fee
    const selectedAcc = document.querySelector('input[name="accommodation"]:checked');
    let accommodationFee = 0;
    let nights = 0;
    if (selectedAcc && selectedAcc.value === 'Require Accommodation') {
      nights = parseInt(accommodationNightsInput.value, 10);
      if (isNaN(nights) || nights < 1) nights = 0;
      accommodationFee = nights * ACCOMMODATION_RATE;
    }

    // 5. Total Calculation
    const totalFee = REGISTRATION_FEE + attendanceFee + workshopFee + mealFee + accommodationFee;

    // Update UI Elements
    if (dispDays) dispDays.textContent = days;
    if (dispMealDays) dispMealDays.textContent = days;
    if (dispNights) dispNights.textContent = nights;

    if (summaryAttendance) summaryAttendance.textContent = formatMoney(attendanceFee);
    if (summaryWorkshop) summaryWorkshop.textContent = formatMoney(workshopFee);
    if (summaryMeals) summaryMeals.textContent = formatMoney(mealFee);
    if (summaryAccommodation) summaryAccommodation.textContent = formatMoney(accommodationFee);
    if (summaryTotal) summaryTotal.textContent = formatMoney(totalFee);
  }

  /**
   * FUNCTION 4: validateForm(event)
   * Client-side validation prior to form submission.
   * Checks all fields, prevents form submission if invalid, displays human-readable error messages.
   * @param {Event} e 
   */
  function validateForm(e) {
    const errors = [];

    // 1. Full Name Validation
    const fullName = fullNameInput.value.trim();
    if (!fullName) {
      errors.push('Please enter your full name.');
    }

    // 2. Admission / Reg Number Validation
    const admissionNumber = admissionNumberInput.value.trim();
    if (!admissionNumber) {
      errors.push('Please enter your admission / registration number.');
    }

    // 3. Email Validation (standard regex)
    const email = emailInput.value.trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
      errors.push('Please enter your email address.');
    } else if (!emailRegex.test(email)) {
      errors.push('Please enter a valid email address.');
    }

    // 4. Phone Number Validation (Exactly 10 digits)
    const phone = phoneInput.value.trim();
    const phoneRegex = /^\d{10}$/;
    if (!phone) {
      errors.push('Please enter your phone number.');
    } else if (!phoneRegex.test(phone)) {
      errors.push('Phone number must contain exactly 10 digits.');
    }

    // 5. Year of Study Validation
    const year = yearSelect.value;
    if (!year) {
      errors.push('Please select your year of study.');
    }

    // 6. Participation Category Validation
    const selectedCategory = document.querySelector('input[name="category"]:checked');
    if (!selectedCategory) {
      errors.push('Please select a participation category.');
    }

    // 7. Conference Days Validation (Must be integer between 1 and 3)
    const daysVal = parseInt(daysInput.value, 10);
    if (isNaN(daysVal) || daysVal < 1 || daysVal > 3) {
      errors.push('Conference attendance must be between 1 and 3 days.');
    }

    // 8. Accommodation Nights Validation (if accommodation is selected)
    const selectedAcc = document.querySelector('input[name="accommodation"]:checked');
    if (selectedAcc && selectedAcc.value === 'Require Accommodation') {
      const nightsVal = parseInt(accommodationNightsInput.value, 10);
      if (isNaN(nightsVal) || nightsVal < 1) {
        errors.push('Please enter a valid number of accommodation nights (at least 1 night).');
      }
    }

    // Process Errors
    if (errors.length > 0) {
      // Prevent HTML Form Submission
      e.preventDefault();

      // Render Error Messages
      errorList.innerHTML = '';
      errors.forEach(err => {
        const li = document.createElement('li');
        li.textContent = err;
        errorList.appendChild(li);
      });

      errorMessageContainer.classList.remove('hidden');

      // Scroll smoothly to error container
      errorMessageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
      return false;
    }

    // Hide error container if valid
    errorMessageContainer.classList.add('hidden');
    return true;
  }

  // --- EVENT LISTENERS ATTACHMENT ---

  // 1. Submit Event
  if (registrationForm) {
    registrationForm.addEventListener('submit', validateForm);
  }

  // 2. Change Event: Participation Category Radios
  const categoryRadios = document.querySelectorAll('input[name="category"]');
  categoryRadios.forEach(radio => {
    radio.addEventListener('change', showWorkshop);
  });

  // 3. Change Event: Accommodation Radios
  const accommodationRadios = document.querySelectorAll('input[name="accommodation"]');
  accommodationRadios.forEach(radio => {
    radio.addEventListener('change', showAccommodation);
  });

  // 4. Input & Change Events: Days & Accommodation Nights
  if (daysInput) {
    daysInput.addEventListener('input', calculateFee);
    daysInput.addEventListener('change', calculateFee);
  }

  if (accommodationNightsInput) {
    accommodationNightsInput.addEventListener('input', calculateFee);
    accommodationNightsInput.addEventListener('change', calculateFee);
  }

  // 5. Change Event: Meal Options
  const mealRadios = document.querySelectorAll('input[name="meals"]');
  mealRadios.forEach(radio => {
    radio.addEventListener('change', calculateFee);
  });

  // 6. Change Event: Workshop Options
  const workshopRadios = document.querySelectorAll('input[name="workshop"]');
  workshopRadios.forEach(radio => {
    radio.addEventListener('change', calculateFee);
  });

  // 7. Click Event: Reset Button
  if (resetBtn) {
    resetBtn.addEventListener('click', function () {
      setTimeout(() => {
        showWorkshop();
        showAccommodation();
        calculateFee();
        errorMessageContainer.classList.add('hidden');
      }, 50);
    });
  }

  // --- INITIALIZATION ---
  showWorkshop();
  showAccommodation();
  calculateFee();

});
