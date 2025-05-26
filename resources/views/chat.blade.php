<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Laravel ChatBot</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.3.1/css/bootstrap.min.css"/>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <style>
    .chatbot-icon {
      position: fixed;
      bottom: 20px;
      right: 20px;
      background-color: #28a745;
      color: white;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      text-align: center;
      font-size: 30px;
      line-height: 60px;
      cursor: pointer;
      z-index: 999;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    }

    .chatbot-container {
      position: fixed;
      bottom: 90px;
      right: 20px;
      width: 350px;
      max-height: 500px;
      background: white;
      border-radius: 15px;
      box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
      display: none;
      flex-direction: column;
      overflow: hidden;
      z-index: 998;
    }

    .chatbot-header {
      background-color: #28a745;
      color: white;
      padding: 12px;
      font-weight: bold;
      font-size: 18px;
      text-align: center;
    }

    .chatbot-body {
      padding: 10px;
      overflow-y: auto;
      flex-grow: 1;
      background-color: #f8f9fa;
    }

    .chatbot-footer {
      padding: 10px;
      background-color: #fff;
      border-top: 1px solid #ddd;
    }

    .chat-bubble {
      padding: 10px 14px;
      border-radius: 20px;
      margin: 6px 0;
      max-width: 80%;
      display: inline-block;
      clear: both;
    }

    .chat-bubble.user {
      background-color: #dcf8c6;
      float: right;
    }

    .chat-bubble.bot {
      background-color: #e9ecef;
      float: left;
    }

    .loading {
      color: #666;
      font-style: italic;
      margin: 5px 0;
    }
  </style>
</head>
<body>

  <!-- Chatbot Icon -->
  <div class="chatbot-icon" id="chatbotToggle">💬</div>

  <!-- Chatbot Container -->
  <div class="chatbot-container d-flex flex-column" id="chatbotContainer">
    <div class="chatbot-header">Laravel ChatBot</div>
    <div class="chatbot-body" id="response">
      <div class="text-muted text-center">Ask me anything!</div>
    </div>
    <div class="chatbot-footer">
      <input type="text" class="form-control mb-2" id="userInput" placeholder="Type your message...">
      <button class="btn btn-success btn-block" id="sendBtn">Send</button>
    </div>
  </div>

  <script>
    // Toggle chatbot open/close
    $('#chatbotToggle').on('click', function () {
      const $chatbot = $('#chatbotContainer');
      if ($chatbot.is(':visible')) {
        $chatbot.slideUp().removeClass('d-flex');
      } else {
        $chatbot.addClass('d-flex').hide().slideDown();
        $('#userInput').focus();
      }
    });

    // Handle sending on Enter
    $('#userInput').on('keypress', function (e) {
      if (e.key === 'Enter') {
        sendMessage();
      }
    });

    // Handle sending on button click
    $('#sendBtn').on('click', sendMessage);

    // Send message function
    function sendMessage() {
      const inputField = $('#userInput');
      const message = inputField.val().trim();
      const responseDiv = $('#response');

      if (!message) return;

      appendMessage(message, 'user');
      appendLoading();
      inputField.val('');

      $.ajax({
        url: '/chat',
        method: 'POST',
        contentType: 'application/json',
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: JSON.stringify({ message }),
        success: function (data) {
          removeLoading();
          const reply = data.error ? `Error: ${data.error}` : marked.parse(data.response);
          appendMessage(reply, 'bot');
        },
        error: function (xhr) {
          removeLoading();
          appendMessage(`Error: ${xhr.statusText}`, 'bot');
        }
      });
    }

    function appendMessage(text, sender) {
      const bubble = $('<div></div>')
        .addClass(`chat-bubble ${sender}`)
        .html(text);
      $('#response').append(bubble).scrollTop($('#response')[0].scrollHeight);
    }

    function appendLoading() {
      const loading = $('<div></div>')
        .attr('id', 'loading')
        .addClass('loading')
        .text('Bot is typing...');
      $('#response').append(loading).scrollTop($('#response')[0].scrollHeight);
    }

    function removeLoading() {
      $('#loading').remove();
    }
  </script>
</body>
</html>
