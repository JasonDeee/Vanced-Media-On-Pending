document.addEventListener("DOMContentLoaded", () => {
  const lightBulb = document.querySelector("#LightBulb");
  const svg = document.querySelector("svg");

  if (!lightBulb || !svg) {
    console.error("Required elements not found");
    return;
  }

  function handleMouseMove(event) {
    const svgRect = svg.getBoundingClientRect();
    const viewBox = svg.viewBox.baseVal;

    // Tính toán tỷ lệ giữa viewBox và kích thước thực tế
    const scaleX = viewBox.width / svgRect.width;
    const scaleY = viewBox.height / svgRect.height;

    // Chuyển đổi tọa độ chuột sang tọa độ SVG
    const mouseX = (event.clientX - svgRect.left) * scaleX;
    const mouseY = (event.clientY - svgRect.top) * scaleY;

    // Offset để căn giữa bóng đèn với con trỏ
    const offsetX = 300;
    const offsetY = 200;

    // Áp dụng transform với offset
    lightBulb.setAttribute(
      "transform",
      `translate(${mouseX - offsetX} ${mouseY - offsetY})`
    );
  }

  svg.addEventListener("mousemove", handleMouseMove);
});
