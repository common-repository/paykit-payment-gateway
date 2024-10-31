document.addEventListener("DOMContentLoaded", function () {
  const inputFields = document.querySelectorAll(
    ".paykit-input-hide-show-password"
  );

  inputFields.forEach(function (input) {
    const toggleButton = document.createElement("i");
    toggleButton.className = "dashicons dashicons-visibility";
    toggleButton.style.cursor = "pointer";
    toggleButton.style.padding = "5px";

    toggleButton.addEventListener("click", function () {
      if (input.type === "password") {
        input.type = "text";
        toggleButton.classList.remove("dashicons-visibility");
        toggleButton.classList.add("dashicons-hidden");
      } else {
        input.type = "password";
        toggleButton.classList.remove("dashicons-hidden");
        toggleButton.classList.add("dashicons-visibility");
      }
    });

    input.insertAdjacentElement("afterend", toggleButton);
  });
});
