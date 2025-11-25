// Unit conversion mappings
const unitConversions = {
  ml: { base: "ml", conversions: { ml: 1, L: 1000 } },
  L: { base: "ml", conversions: { ml: 1, L: 1000 } },
  g: { base: "g", conversions: { g: 1, kg: 1000 } },
  kg: { base: "g", conversions: { g: 1, kg: 1000 } },
  pcs: { base: "pcs", conversions: { pcs: 1 } },
  units: { base: "units", conversions: { units: 1 } },
};

function getUnitFamily(unit) {
  if (unit === "ml" || unit === "L") return ["ml", "L"];
  if (unit === "g" || unit === "kg") return ["g", "kg"];
  return [unit];
}

function convertValue(value, fromUnit, toUnit) {
  if (fromUnit === toUnit) return value;

  const fromConfig = unitConversions[fromUnit];
  const toConfig = unitConversions[toUnit];

  if (!fromConfig || !toConfig || fromConfig.base !== toConfig.base) {
    return value; // Can't convert between incompatible units
  }

  // Convert to base unit first, then to target unit
  const baseValue = value * fromConfig.conversions[fromUnit];
  return baseValue / toConfig.conversions[toUnit];
}

function formatQuantity(value) {
  // Remove trailing zeros and unnecessary decimal points
  return parseFloat(value.toFixed(2)).toString();
}

function openRestockModal(productId, productName, currentStock, unit) {
  // Store base information
  document.getElementById("restock_product_id").value = productId;
  document.getElementById("restock_product_name").textContent = productName;
  document.getElementById("restock_base_unit").value = unit;
  document.getElementById("restock_base_quantity").value = currentStock;
  document.getElementById("restock_base_unit_display").textContent = unit;

  // Get unit family for this product
  const unitFamily = getUnitFamily(unit);

  // Populate display unit selector
  const displayUnitSelect = document.getElementById("restock_display_unit");
  displayUnitSelect.innerHTML = "";
  unitFamily.forEach((u) => {
    const option = document.createElement("option");
    option.value = u;
    option.textContent = u;
    option.selected = u === unit;
    displayUnitSelect.appendChild(option);
  });

  // Populate input unit selector
  const inputUnitSelect = document.getElementById("restock_input_unit");
  inputUnitSelect.innerHTML = "";
  unitFamily.forEach((u) => {
    const option = document.createElement("option");
    option.value = u;
    option.textContent = u;
    option.selected = u === unit;
    inputUnitSelect.appendChild(option);
  });

  // Display current stock
  updateDisplayUnit();

  // Show modal
  document.getElementById("restockModal").classList.remove("hidden");
}

function updateDisplayUnit() {
  const baseQuantity = parseFloat(
    document.getElementById("restock_base_quantity").value,
  );
  const baseUnit = document.getElementById("restock_base_unit").value;
  const displayUnit = document.getElementById("restock_display_unit").value;

  const convertedQuantity = convertValue(baseQuantity, baseUnit, displayUnit);

  document.getElementById("restock_current_stock").textContent =
    formatQuantity(convertedQuantity);
  document.getElementById("restock_current_unit").textContent = displayUnit;
}

function closeRestockModal() {
  document.getElementById("restockModal").classList.add("hidden");
  document.getElementById("restockForm").reset();
}

function openAdjustModal(ingredientId, ingredientName, currentStock, unit) {
  // Store base information
  document.getElementById("adjust_product_id").value = ingredientId;
  document.getElementById("adjust_product_name").textContent = ingredientName;
  document.getElementById("adjust_base_unit").value = unit;
  document.getElementById("adjust_base_quantity").value = currentStock;
  document.getElementById("adjust_base_unit_display").textContent = unit;

  // Get unit family for this product
  const unitFamily = getUnitFamily(unit);

  // Populate display unit selector
  const displayUnitSelect = document.getElementById("adjust_display_unit");
  displayUnitSelect.innerHTML = "";
  unitFamily.forEach((u) => {
    const option = document.createElement("option");
    option.value = u;
    option.textContent = u;
    option.selected = u === unit;
    displayUnitSelect.appendChild(option);
  });

  // Populate input unit selector
  const inputUnitSelect = document.getElementById("adjust_input_unit");
  inputUnitSelect.innerHTML = "";
  unitFamily.forEach((u) => {
    const option = document.createElement("option");
    option.value = u;
    option.textContent = u;
    option.selected = u === unit;
    inputUnitSelect.appendChild(option);
  });

  // Reset to add mode
  setAdjustmentType("add");

  // Display current stock
  updateAdjustDisplayUnit();

  // Show modal
  document.getElementById("adjustModal").classList.remove("hidden");
}

function closeAdjustModal() {
  document.getElementById("adjustModal").classList.add("hidden");
  document.getElementById("adjustForm").reset();
}

function updateAdjustDisplayUnit() {
  const baseQuantity = parseFloat(
    document.getElementById("adjust_base_quantity").value,
  );
  const baseUnit = document.getElementById("adjust_base_unit").value;
  const displayUnit = document.getElementById("adjust_display_unit").value;

  const convertedQuantity = convertValue(baseQuantity, baseUnit, displayUnit);

  document.getElementById("adjust_current_stock").textContent =
    formatQuantity(convertedQuantity);
  document.getElementById("adjust_current_unit").textContent = displayUnit;
}

function setAdjustmentType(type) {
  document.getElementById("adjustment_type").value = type;
  
  const addBtn = document.getElementById("adjust_type_add");
  const subtractBtn = document.getElementById("adjust_type_subtract");
  
  if (type === "add") {
    addBtn.className = "px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500";
    subtractBtn.className = "px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400";
  } else {
    addBtn.className = "px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400";
    subtractBtn.className = "px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500";
  }
}

function submitAdjust(event) {
  event.preventDefault();

  const ingredientId = document.getElementById("adjust_product_id").value;
  const quantity = parseFloat(document.getElementById("adjust_quantity").value);
  const inputUnit = document.getElementById("adjust_input_unit").value;
  const baseUnit = document.getElementById("adjust_base_unit").value;
  const notes = document.getElementById("adjust_notes").value;
  const adjustmentType = document.getElementById("adjustment_type").value;

  // Convert input quantity to base unit for storage
  let baseQuantity = convertValue(quantity, inputUnit, baseUnit);
  
  // Make negative if subtracting
  if (adjustmentType === "subtract") {
    baseQuantity = -baseQuantity;
  }

  fetch("update_inventory.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ingredient_id: ingredientId,
      quantity: baseQuantity,
      type: "adjustment",
      notes: notes,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        closeAdjustModal();
        showSuccessModal("Stock adjusted successfully!");
      } else {
        showErrorModal(data.message || "Failed to adjust stock");
      }
    })
    .catch((error) => {
      showErrorModal("An error occurred: " + error.message);
    });
}

function submitRestock(event) {
  event.preventDefault();

  const ingredientId = document.getElementById("restock_product_id").value;
  const quantity = parseFloat(document.getElementById("restock_quantity").value);
  const inputUnit = document.getElementById("restock_input_unit").value;
  const baseUnit = document.getElementById("restock_base_unit").value;
  const notes = document.getElementById("restock_notes").value;

  // Convert input quantity to base unit for storage
  const baseQuantity = convertValue(quantity, inputUnit, baseUnit);

  fetch("update_inventory.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ingredient_id: ingredientId,
      quantity: baseQuantity,
      type: "restock",
      notes: notes,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        closeRestockModal();
        showSuccessModal("Stock updated successfully!");
      } else {
        showErrorModal(data.message || "Failed to update stock");
      }
    })
    .catch((error) => {
      showErrorModal("An error occurred: " + error.message);
    });
}

function adjustStock(ingredientId, adjustment) {
  fetch("update_inventory.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      ingredient_id: ingredientId,
      quantity: adjustment,
      type: "adjustment",
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        showSuccessModal("Stock adjusted successfully!");
      } else {
        showErrorModal(data.message || "Failed to adjust stock");
      }
    })
    .catch((error) => {
      showErrorModal("An error occurred: " + error.message);
    });
}

