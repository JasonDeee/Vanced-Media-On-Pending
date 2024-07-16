function doPost(e) {
  // Lấy Spreadsheet hiện tại
  var spreadsheet = SpreadsheetApp.getActiveSpreadsheet();

  // Chọn sheet "Donater List"
  var sheet = spreadsheet.getSheetByName("Donater List");

  // Lấy dữ liệu từ yêu cầu POST
  var data = e.parameter;
  var name = data.Name; // Lấy dữ liệu từ input có name="Name"
  var message = data.Message; // Lấy dữ liệu từ textarea có name="Message"

  // Thêm dữ liệu vào sheet
  sheet.appendRow([name, message]);

  // Trả về phản hồi (tuỳ chọn - bạn có thể điều hướng đến trang khác)
  return ContentService.createTextOutput(
    "Ref=" + e.referrer + "Ip Add: " + e.clientInfo.ipAddress
  );
}
