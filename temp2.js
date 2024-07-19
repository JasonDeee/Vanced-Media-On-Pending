const QRData = {
  accountNo: 1043374358,
  accountName: "VANCED",
  acqId: 970436,
  amount: 321000,
  addInfo: "HeheBoizz",
  format: "text",
  template: "qIRXByI",
};
fetch("https://api.vietqr.io/v2/generate", {
  method: "POST",
  headers: {
    "x-client-id": "96a6c861-3012-4a20-aae1-da3955979482",
    "x-api-key": "984f47ea-7d4e-4255-858e-310c5622fb9d",
    "Content-Type": "application/json",
  },
  body: JSON.stringify(QRData),
})
  .then((response) => response.json())
  .then((Resdata) => {
    // Xử lý phản hồi từ Apps Script (nếu cần)
    console.log(Resdata.data.qrCode);
    // https://qrcode-monkey.p.rapidapi.com/qr/custom
    fetch(
      "https://api.qr-code-generator.com/v1/create?access-token=UlzTGGuNVK9t0jQkBj73Z3AQz12szj4eWevxkMMJh1H1CqGBli4rRixv49hoyOtd",
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json", // Bắt buộc để gửi dữ liệu JSON
          "x-rapidapi-host": "qrcode-monkey.p.rapidapi.com",
          "x-rapidapi-key":
            "4bec191dfcmsh75ea856d8f80f60p15e66bjsn44adbd77af9d",
        },
        body: {
          data: Resdata.data.qrCode,
          config: {
            body: "circle",
          },
          size: 300,
          download: false,
          file: "svg",
        },
      }
    ).then((MonkeyQRRes) => {
      console.log(MonkeyQRRes);
    });
    //
  })
  .then((response) => {})
  .catch((error) => {
    console.error("Lỗi khi gửi dữ liệu:", error);
  });
