const TheMainBody = document.body;
const main = document.getElementById("main_scroll");

const ScrollResize = () => {
  TheMainBody.style.height = `${bodyHeight}px`;
  clearTimeout(resizetime);

  resizetime = setTimeout(() => {
    bodyHeight = Math.round(main.getBoundingClientRect().height);
    TheMainBody.style.height = `${bodyHeight}px`;
    easeScroll();

    clearTimeout(resizetime);
  }, 1200);
};
ScrollResize();
window.addEventListener("resize", ScrollResize);

const li = (a, b, n) => {
  return (1 - n) * a + n * b;
};

var sx = 0, // For scroll positions
  sy = 0;
var dx = sx, // For container positions And Force (Percentage 70% Recommended)
  dy = sy,
  Force = 80;

function easeScroll() {
  // sx = window.pageXOffset;
  sy = window.pageYOffset;
  // console.log(sy);
}

window.requestAnimationFrame(render);
function render() {
  //We calculate our container position by linear interpolation method
  // dx = li(dx, sx, Force / 1000);
  dy = li(dy, sy, Force / 1000);

  // dx = Math.floor(dx * 100) / 100;
  dy = Math.round(dy * 100) / 100;
}
window.addEventListener("scroll", (e) => {
  easeScroll(); // Momentium
});
