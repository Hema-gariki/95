import requests

url = "http://localhost/project_backend/get_user_info.php"
user_id = 1
data = {'user_id'}: user_id
try:
   response = requests.post(url, data=data)
   if response.status_code == 200:
      user_data = response.json()
      print("user Data:", user_data)
   else:
      print(f"failed to retrieve data. Status code:{response.status_code}")
      print("error:", response.json().get('error','unknown error'))
except requests.exceptions.RequestException as e:
   print("An error occured:" , e)