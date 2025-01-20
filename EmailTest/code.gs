/**
 * Xử lý POST request từ form HTML
 */
function doPost(e) {
  // Thêm CORS headers
  const headers = {
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "POST",
    "Access-Control-Allow-Headers": "Content-Type",
  };

  try {
    // Parse dữ liệu từ request
    const data = JSON.parse(e.postData.contents);
    const email = data.email;
    const timestamp = new Date();

    // Kiểm tra rate limiting
    const cache = CacheService.getScriptCache();
    const emailKey = `email_${email}`;
    const lastSubmit = cache.get(emailKey);

    // Kiểm tra xem email này đã submit trong 24h gần đây chưa
    if (lastSubmit) {
      throw new Error(
        "Email này đã được đăng ký. Vui lòng thử lại sau 24 giờ."
      );
    }

    // Lấy spreadsheet và sheet
    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const ws = ss.getSheets()[0];

    if (!ws) {
      throw new Error("Không tìm thấy sheet");
    }

    // Kiểm tra xem email đã tồn tại trong sheet chưa
    const sheetData = ws.getDataRange().getValues();
    const emailExists = sheetData.some((row) => row[0] === email);

    if (emailExists) {
      throw new Error("Email này đã được đăng ký trước đó!");
    }

    // Lưu email và timestamp vào sheet
    ws.appendRow([email, timestamp]);

    // Lưu vào cache để rate limiting (24 giờ = 86400 giây)
    cache.put(emailKey, timestamp.toString(), 86400);

    // Trả về response thành công với CORS headers
    return ContentService.createTextOutput(
      JSON.stringify({
        status: "success",
        message: "Đăng ký thành công!",
      })
    )
      .setMimeType(ContentService.MimeType.JSON)
      .setHeaders(headers);
  } catch (error) {
    // Trả về response lỗi với CORS headers
    return ContentService.createTextOutput(
      JSON.stringify({
        status: "error",
        message: error.toString(),
      })
    )
      .setMimeType(ContentService.MimeType.JSON)
      .setHeaders(headers);
  }
}

/**
 * Xử lý OPTIONS request cho CORS preflight
 */
function doOptions(e) {
  const headers = {
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "POST",
    "Access-Control-Allow-Headers": "Content-Type",
    "Access-Control-Max-Age": "3600",
  };

  return ContentService.createTextOutput("")
    .setMimeType(ContentService.MimeType.TEXT)
    .setHeaders(headers);
}

/**
 * Xử lý GET request để kiểm tra web app hoạt động
 */
function doGet(e) {
  const email = e.parameter.email;

  try {
    const timestamp = new Date();
    const cache = CacheService.getScriptCache();
    const emailKey = `email_${email}`;
    const lastSubmit = cache.get(emailKey);

    if (lastSubmit) {
      throw new Error(
        "Email này đã được đăng ký. Vui lòng thử lại sau 24 giờ."
      );
    }

    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const ws = ss.getSheets()[0];

    if (!ws) {
      throw new Error("Không tìm thấy sheet");
    }

    const sheetData = ws.getDataRange().getValues();
    const emailExists = sheetData.some((row) => row[0] === email);

    if (emailExists) {
      throw new Error("Email này đã được đăng ký trước đó!");
    }

    ws.appendRow([email, timestamp]);
    cache.put(emailKey, timestamp.toString(), 86400);

    return ContentService.createTextOutput(
      `callback(${JSON.stringify({
        status: "success",
        message: "Đăng ký thành công!",
      })})`
    ).setMimeType(ContentService.MimeType.JAVASCRIPT);
  } catch (error) {
    return ContentService.createTextOutput(
      `callback(${JSON.stringify({
        status: "error",
        message: error.toString(),
      })})`
    ).setMimeType(ContentService.MimeType.JAVASCRIPT);
  }
}

/**
 * Hàm setup ban đầu (chạy một lần)
 */
function setup() {
  // Tạo sheet mới nếu chưa có
  const ss = SpreadsheetApp.getActiveSpreadsheet();
  let ws = ss.getSheets()[0];

  if (!ws) {
    ws = ss.insertSheet("Đăng ký tại Footer");
    // Tạo header cho sheet
    ws.appendRow(["Email", "Timestamp"]);
  }
}

function processForm(email) {
  try {
    const timestamp = new Date();

    // Kiểm tra rate limiting
    const cache = CacheService.getScriptCache();
    const emailKey = `email_${email}`;
    const lastSubmit = cache.get(emailKey);

    if (lastSubmit) {
      throw new Error(
        "Email này đã được đăng ký. Vui lòng thử lại sau 24 giờ."
      );
    }

    const ss = SpreadsheetApp.getActiveSpreadsheet();
    const ws = ss.getSheets()[0];

    if (!ws) {
      throw new Error("Không tìm thấy sheet");
    }

    // Kiểm tra email trùng lặp
    const sheetData = ws.getDataRange().getValues();
    const emailExists = sheetData.some((row) => row[0] === email);

    if (emailExists) {
      throw new Error("Email này đã được đăng ký trước đó!");
    }

    // Lưu dữ liệu
    ws.appendRow([email, timestamp]);
    cache.put(emailKey, timestamp.toString(), 86400);

    return true;
  } catch (error) {
    throw error;
  }
}
