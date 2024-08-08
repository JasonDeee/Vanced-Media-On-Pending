const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(
  navigator.userAgent
)
  ? true
  : false;

const TheMainBody = document.body;
const MainScroll = document.getElementById("main_scroll");

var resizetime;

const ScrollResize = () => {
  let bodyHeight = Math.round(MainScroll.getBoundingClientRect().height);

  TheMainBody.style.height = `${bodyHeight}px`;
  clearTimeout(resizetime);

  resizetime = setTimeout(() => {
    bodyHeight = Math.round(MainScroll.getBoundingClientRect().height);
    TheMainBody.style.height = `${bodyHeight}px`;

    clearTimeout(resizetime);
  }, 1200);
};
ScrollResize();
window.addEventListener("resize", ScrollResize);

if (!isMobile) {
  const ScrollFunc = () => {
    MainScroll.style.transform = `translateY(-${window.pageYOffset}px)`;
  };
  window.addEventListener("scroll", ScrollFunc);
}
