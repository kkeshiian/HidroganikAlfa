' Script untuk menjalankan MQTT Bridge di background (tanpa window)
' Double-click file ini untuk menjalankan bridge secara invisible

Set WshShell = CreateObject("WScript.Shell")
Set fso = CreateObject("Scripting.FileSystemObject")

' Get path of batch file
scriptPath = fso.GetParentFolderName(WScript.ScriptFullName)
batFile = scriptPath & "\start-bridge.bat"

' Check if batch file exists
If Not fso.FileExists(batFile) Then
    MsgBox "Error: start-bridge.bat tidak ditemukan!" & vbCrLf & "Path: " & batFile, vbCritical, "MQTT Bridge"
    WScript.Quit
End If

' Run batch file hidden (0 = hidden, 1 = normal)
WshShell.Run chr(34) & batFile & chr(34), 0, False

' Show notification
MsgBox "MQTT Bridge sudah berjalan di background" & vbCrLf & vbCrLf & _
       "Data sensor akan otomatis tersimpan ke database" & vbCrLf & _
       "Untuk menghentikan, buka Task Manager dan end process 'node.exe'", vbInformation, "Hidroganik MQTT Bridge"
